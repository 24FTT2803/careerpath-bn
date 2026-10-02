<?php

namespace App\Console\Commands;

use App\Contracts\ChatTransport;
use Illuminate\Console\Command;
use Throwable;

/**
 * Check that the configured inference provider can do what the
 * career clients need, before a student finds out that it cannot.
 *
 * Both career clients send a strict JSON schema and throw when the
 * reply does not conform. A provider that accepts the request but
 * ignores response_format therefore fails at the worst possible
 * moment. This sends one request of the same shape so that failure
 * surfaces here instead.
 */
class ProbeCareerAiTransport extends Command
{
    protected $signature = 'career-ai:probe';

    protected $description =
        'Send one schema-constrained request through the configured AI provider.';

    public function handle(ChatTransport $transport): int
    {
        $provider = (string) config('career-ai.transport');

        $this->components->twoColumnDetail(
            'Transport',
            $provider
        );

        $this->components->twoColumnDetail(
            'Model',
            (string) config(
                'career-ai.'.$provider.'.model',
                'unknown'
            )
        );

        $this->newLine();

        try {
            $result = $transport->chat(
                $this->messages(),
                $this->responseFormat(),
                0.0
            );
        } catch (Throwable $exception) {
            $this->components->error(
                'The request failed: '.$exception->getMessage()
            );

            return self::FAILURE;
        }

        return $this->report($result);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function report(array $result): int
    {
        $decoded = json_decode(
            (string) ($result['content'] ?? ''),
            true
        );

        /*
         * Valid JSON is the whole point of the probe. A provider
         * that returns prose here would break every recommendation
         * and every adviser answer.
         */
        if (! is_array($decoded) || ! isset($decoded['verdict'])) {
            $this->components->error(
                'The provider replied, but not in the requested schema.'
            );

            $this->line(
                '  Reply: '
                .mb_strimwidth(
                    (string) ($result['content'] ?? ''),
                    0,
                    200,
                    '…'
                )
            );

            $this->newLine();

            $this->line(
                '  This provider cannot be used for CareerPath BN.'
            );

            return self::FAILURE;
        }

        $this->components->info(
            'Schema honoured. This provider can serve CareerPath BN.'
        );

        $this->components->twoColumnDetail(
            'Served by',
            (string) ($result['model'] ?? 'not reported')
        );

        $usage = $result['usage'] ?? null;

        if (is_array($usage)) {
            $this->components->twoColumnDetail(
                'Tokens',
                ($usage['prompt_tokens'] ?? '?')
                .' in / '
                .($usage['completion_tokens'] ?? '?')
                .' out'
            );
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function messages(): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'Reply only with the requested JSON object.',
            ],
            [
                'role' => 'user',
                'content' => 'Set verdict to "ok" and reason to a short sentence.',
            ],
        ];
    }

    /**
     * Deliberately the same strictness the career clients use, so
     * passing here means passing there.
     *
     * @return array<string, mixed>
     */
    private function responseFormat(): array
    {
        return [
            'type' => 'json_schema',

            'json_schema' => [
                'name' => 'transport_probe',
                'strict' => true,

                'schema' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['verdict', 'reason'],

                    'properties' => [
                        'verdict' => [
                            'type' => 'string',
                            'enum' => ['ok'],
                        ],

                        'reason' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ];
    }
}
