<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

class GroqClient
{
    /**
     * Send a chat completion request to Groq.
     */
    public function chat(
        array $messages,
        ?array $responseFormat = null
    ): array {
        $baseUrl = rtrim(
            (string) config(
                'career-ai.groq.base_url',
                ''
            ),
            '/'
        );

        $endpoint = trim(
            (string) config(
                'career-ai.groq.endpoint',
                ''
            )
        );

        $apiKey = trim(
            (string) config(
                'career-ai.groq.api_key',
                ''
            )
        );

        $model = trim(
            (string) config(
                'career-ai.groq.model',
                ''
            )
        );

        $reasoningEffort = trim(
            (string) config(
                'career-ai.groq.reasoning_effort',
                'medium'
            )
        );

        $timeout = max(
            1,
            (int) config(
                'career-ai.timeout',
                30
            )
        );

        $connectTimeout = max(
            1,
            (int) config(
                'career-ai.connect_timeout',
                10
            )
        );

        if ($baseUrl === '') {
            throw new RuntimeException(
                'Groq base URL is not configured.'
            );
        }

        if ($endpoint === '') {
            throw new RuntimeException(
                'Groq chat endpoint is not configured.'
            );
        }

        if ($apiKey === '') {
            throw new RuntimeException(
                'Groq API key is not configured.'
            );
        }

        if ($model === '') {
            throw new RuntimeException(
                'Groq model is not configured.'
            );
        }

        $url = $baseUrl
            . '/'
            . ltrim(
                $endpoint,
                '/'
            );

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        if ($reasoningEffort !== '') {
            $payload['reasoning_effort'] =
                $reasoningEffort;
        }

        if ($responseFormat !== null) {
            $payload['response_format'] =
                $responseFormat;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->connectTimeout(
                $connectTimeout
            )
            ->timeout(
                $timeout
            )
            ->post(
                $url,
                $payload
            );

        $response->throw();

        $data = $response->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException(
                'Groq returned an invalid JSON response.'
            );
        }

        $content = data_get(
            $data,
            'choices.0.message.content'
        );

        if (
            ! is_string($content)
            || trim($content) === ''
        ) {
            throw new UnexpectedValueException(
                'Groq returned a response without message content.'
            );
        }

        return [
            'content' => $content,

            'model' => is_string(
                $data['model'] ?? null
            )
                ? $data['model']
                : $model,

            'usage' => is_array(
                $data['usage'] ?? null
            )
                ? $data['usage']
                : null,
        ];
    }
}