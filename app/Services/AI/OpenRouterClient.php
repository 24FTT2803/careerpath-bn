<?php

namespace App\Services\AI;

use App\Contracts\ChatTransport;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

/**
 * Chat completions through OpenRouter.
 *
 * OpenRouter is a router rather than a single provider: one model
 * may be served by a dozen upstreams, and they do not all honour
 * the same parameters. That matters here because both career
 * clients send a strict JSON schema and throw when the reply does
 * not conform, so a request that lands on an upstream ignoring
 * response_format fails rather than degrades.
 *
 * require_parameters is what prevents that. It restricts routing
 * to upstreams that actually support everything in the request.
 */
class OpenRouterClient implements ChatTransport
{
    public function chat(
        array $messages,
        ?array $responseFormat = null,
        ?float $temperature = null
    ): array {
        $baseUrl = rtrim(
            (string) config('career-ai.openrouter.base_url', ''),
            '/'
        );

        $endpoint = trim(
            (string) config('career-ai.openrouter.endpoint', '')
        );

        $apiKey = trim(
            (string) config('career-ai.openrouter.api_key', '')
        );

        $model = trim(
            (string) config('career-ai.openrouter.model', '')
        );

        $this->assertConfigured(
            $baseUrl,
            $endpoint,
            $apiKey,
            $model
        );

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        $temperature ??= config(
            'career-ai.openrouter.temperature'
        );

        if ($temperature !== null) {
            $payload['temperature'] = (float) $temperature;
        }

        /*
         * Capped on purpose. Left to its own default, GPT-OSS can
         * spend the entire output budget reasoning about a long
         * prompt and return an empty message, which is a failure
         * rather than a short answer. Sent through OpenRouter's
         * own "reasoning" parameter rather than the raw
         * reasoning_effort field, so it is translated per upstream
         * and ignored by models that do not reason, instead of
         * being rejected by them.
         */
        $reasoningEffort = trim(
            (string) config(
                'career-ai.openrouter.reasoning_effort',
                'medium'
            )
        );

        if ($reasoningEffort !== '') {
            $payload['reasoning'] = [
                'effort' => $reasoningEffort,
            ];
        }

        /*
         * Room for the answer after the reasoning. Omitted when
         * unset so the upstream's own ceiling applies.
         */
        $maxTokens = (int) config(
            'career-ai.openrouter.max_tokens',
            0
        );

        if ($maxTokens > 0) {
            $payload['max_tokens'] = $maxTokens;
        }

        $provider = [];

        /*
         * The same model is served by a dozen upstreams at prices
         * that differ several-fold. Sorting by price keeps the
         * running cost at the floor of that range, which is the
         * difference between a demo that is affordable and one
         * that is not.
         */
        $sort = trim(
            (string) config(
                'career-ai.openrouter.provider_sort',
                ''
            )
        );

        if ($sort !== '') {
            $provider['sort'] = $sort;
        }

        /*
         * Upstreams that serve this model badly rather than not at
         * all. One returned GPT-OSS's thinking in the reasoning
         * field and left the message empty, which reads as a
         * normal completion and fails every request. Excluding
         * them keeps price sorting and failover across the rest.
         */
        $ignored = $this->nameList('provider_ignore');

        if ($ignored !== []) {
            $provider['ignore'] = $ignored;
        }

        /*
         * The opposite choice: name the upstreams allowed to serve
         * this, in order of preference, and accept no others. One
         * known-good upstream beats a cheap one that has to be
         * re-verified every time routing moves, so this is worth
         * the price difference while the behaviour of a reply
         * matters more than its cost. Listing several keeps a
         * fallback; listing one means an outage there is an
         * outage here.
         */
        $only = $this->nameList('provider_only');

        if ($only !== []) {
            $provider['only'] = $only;
        }

        if ($responseFormat !== null) {
            $payload['response_format'] = $responseFormat;

            /*
             * Only applied when a schema is actually being asked
             * for. Narrowing the pool of upstreams costs
             * availability, so it is not worth paying when the
             * reply is free-form anyway.
             */
            if (config(
                'career-ai.openrouter.require_parameters',
                true
            )) {
                $provider['require_parameters'] = true;
            }
        }

        if ($provider !== []) {
            $payload['provider'] = $provider;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->withHeaders($this->attributionHeaders())
            ->connectTimeout(
                max(1, (int) config('career-ai.connect_timeout', 10))
            )
            ->timeout(
                max(1, (int) config('career-ai.timeout', 30))
            )
            ->post($baseUrl.'/'.ltrim($endpoint, '/'), $payload);

        $response->throw();

        return $this->readReply($response->json());
    }

    /**
     * A comma-separated setting read as a list of upstream names.
     *
     * @return array<int, string>
     */
    private function nameList(string $key): array
    {
        return array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        (string) config(
                            'career-ai.openrouter.'.$key,
                            ''
                        )
                    )
                ),
                fn (string $name): bool => $name !== ''
            )
        );
    }

    /**
     * OpenRouter asks for these so requests can be attributed.
     * They are optional, and nothing breaks without them.
     *
     * @return array<string, string>
     */
    private function attributionHeaders(): array
    {
        $headers = [];

        $referer = trim(
            (string) config('career-ai.openrouter.site_url', '')
        );

        $title = trim(
            (string) config('career-ai.openrouter.site_name', '')
        );

        if ($referer !== '') {
            $headers['HTTP-Referer'] = $referer;
        }

        if ($title !== '') {
            $headers['X-Title'] = $title;
        }

        return $headers;
    }

    private function assertConfigured(
        string $baseUrl,
        string $endpoint,
        string $apiKey,
        string $model
    ): void {
        $missing = match (true) {
            $baseUrl === '' => 'base URL',
            $endpoint === '' => 'chat endpoint',
            $apiKey === '' => 'API key',
            $model === '' => 'model',
            default => null,
        };

        if ($missing !== null) {
            throw new RuntimeException(
                'OpenRouter '.$missing.' is not configured.'
            );
        }
    }

    /**
     * @return array{
     *     content: string,
     *     model: string|null,
     *     usage: array<string, mixed>|null
     * }
     */
    private function readReply(mixed $data): array
    {
        if (! is_array($data)) {
            throw new UnexpectedValueException(
                'OpenRouter returned an invalid JSON response.'
            );
        }

        /*
         * A router reports upstream failures in the body with a
         * 200, so an error here is not visible to throw().
         */
        $error = data_get($data, 'error.message');

        if (is_string($error) && $error !== '') {
            throw new UnexpectedValueException(
                'OpenRouter reported an error: '.$error
            );
        }

        $content = data_get($data, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            /*
             * Empty content has one usual cause: the model spent
             * its output budget reasoning and never reached an
             * answer. Saying which upstream, why it stopped, and
             * how much reasoning came back turns a dead end into
             * something diagnosable.
             */
            throw new UnexpectedValueException(
                'OpenRouter returned no message content (provider: '
                .(data_get($data, 'provider') ?: 'unreported')
                .', finish_reason: '
                .(data_get($data, 'choices.0.finish_reason') ?: 'unreported')
                .', reasoning characters: '
                .mb_strlen(
                    (string) data_get(
                        $data,
                        'choices.0.message.reasoning',
                        ''
                    )
                )
                .'). Lower OPENROUTER_REASONING_EFFORT or raise '
                .'OPENROUTER_MAX_TOKENS.'
            );
        }

        /*
         * Which upstream answered, where the router reports it.
         * Price sorting makes this vary between requests, so it is
         * worth surfacing rather than assuming.
         */
        $provider = data_get($data, 'provider');

        return [
            'content' => $content,

            'model' => is_string(data_get($data, 'model'))
                ? data_get($data, 'model')
                    .(is_string($provider) && $provider !== ''
                        ? ' via '.$provider
                        : '')
                : null,

            'usage' => is_array($data['usage'] ?? null)
                ? $data['usage']
                : null,
        ];
    }
}
