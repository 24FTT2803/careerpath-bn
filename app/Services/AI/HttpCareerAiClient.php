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
        $baseUrl = config('career-ai.base_url');
        $endpoint = config('career-ai.endpoint');
        $apiKey = config('career-ai.api_key');
        $timeout = config('career-ai.timeout');

        if (! $baseUrl) {
            throw new RuntimeException(
                'Career AI base URL is not configured.'
            );
        }

        $url = rtrim($baseUrl, '/')
            . '/'
            . ltrim($endpoint, '/');

        $request = Http::acceptJson()
            ->asJson()
            ->timeout($timeout);

        if ($apiKey) {
            $request = $request->withToken($apiKey);
        }

        $response = $request
            ->post($url, $payload)
            ->throw();

        $data = $response->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException(
                'Career AI API returned an invalid JSON response.'
            );
        }

        return $data;
    }
}