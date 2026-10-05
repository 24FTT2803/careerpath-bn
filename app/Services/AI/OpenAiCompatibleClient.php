<?php

namespace App\Services\AI;

use App\Contracts\ChatTransport;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

/**
 * Chat completions against any provider speaking OpenAI's API.
 *
 * Nearly every inference host exposes the same /chat/completions
 * contract, differing only in hostname, key and model string. One
 * class reading a named config block therefore covers all of them,
 * so adding a provider is configuration rather than code.
 *
 * Groq and OpenRouter keep their own classes because each sends
 * something extra — reasoning_effort by default, provider routing —
 * that the others neither need nor accept.
 */
class OpenAiCompatibleClient implements ChatTransport
{
    /**
     * @param  string  $provider  Config block to read, e.g.
     *                            "deepinfra" for career-ai.deepinfra.*
     */
    public function __construct(
        private string $provider
    ) {}

    public function chat(
        array $messages,
        ?array $responseFormat = null,
        ?float $temperature = null
    ): array {
        $baseUrl = rtrim($this->setting('base_url'), '/');
        $endpoint = $this->setting('endpoint', '/chat/completions');
        $apiKey = $this->setting('api_key');
        $model = $this->setting('model');

        $this->assertConfigured($baseUrl, $apiKey, $model);

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        $temperature ??= config(
            'career-ai.'.$this->provider.'.temperature'
        );

        if ($temperature !== null) {
            $payload['temperature'] = (float) $temperature;
        }

        if ($responseFormat !== null) {
            $payload['response_format'] = $responseFormat;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->connectTimeout(
                max(1, (int) config('career-ai.connect_timeout', 10))
            )
            ->timeout(
                max(1, (int) config('career-ai.timeout', 30))
            )
            ->post($baseUrl.'/'.ltrim($endpoint, '/'), $payload);

        $response->throw();

        return $this->readReply($response->json(), $model);
    }

    private function setting(
        string $key,
        string $default = ''
    ): string {
        return trim(
            (string) config(
                'career-ai.'.$this->provider.'.'.$key,
                $default
            )
        );
    }

    private function assertConfigured(
        string $baseUrl,
        string $apiKey,
        string $model
    ): void {
        $missing = match (true) {
            $baseUrl === '' => 'base URL',
            $apiKey === '' => 'API key',
            $model === '' => 'model',
            default => null,
        };

        if ($missing !== null) {
            throw new RuntimeException(
                'Career AI provider "'
                .$this->provider
                .'" has no '
                .$missing
                .' configured.'
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
    private function readReply(mixed $data, string $model): array
    {
        if (! is_array($data)) {
            throw new UnexpectedValueException(
                $this->provider.' returned an invalid JSON response.'
            );
        }

        $content = data_get($data, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new UnexpectedValueException(
                $this->provider
                .' returned a response without message content.'
            );
        }

        return [
            'content' => $content,

            'model' => is_string($data['model'] ?? null)
                ? $data['model']
                : $model,

            'usage' => is_array($data['usage'] ?? null)
                ? $data['usage']
                : null,
        ];
    }
}
