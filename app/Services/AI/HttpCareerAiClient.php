<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

class HttpCareerAiClient implements CareerAiClient
{
    /**
     * Send the student profile payload to the real Career AI API.
     */
    public function recommend(array $payload): array
    {
        $baseUrl = trim(
            (string) config(
                'career-ai.base_url',
                ''
            )
        );

        $endpoint = trim(
            (string) config(
                'career-ai.endpoint',
                ''
            )
        );

        $apiKey = config(
            'career-ai.api_key'
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
                'Career AI base URL is not configured.'
            );
        }

        if ($endpoint === '') {
            throw new RuntimeException(
                'Career AI endpoint is not configured.'
            );
        }

        $url = rtrim(
            $baseUrl,
            '/'
        )
            . '/'
            . ltrim(
                $endpoint,
                '/'
            );

        $request = Http::acceptJson()
            ->asJson()
            ->connectTimeout(
                $connectTimeout
            )
            ->timeout(
                $timeout
            );

        if (
            is_string($apiKey)
            && trim($apiKey) !== ''
        ) {
            $request = $request->withToken(
                trim($apiKey)
            );
        }

        $response = $request->post(
            $url,
            $payload
        );

        $response->throw();

        $data = $response->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException(
                'Career AI API returned an invalid JSON response.'
            );
        }

        return $data;
    }
}