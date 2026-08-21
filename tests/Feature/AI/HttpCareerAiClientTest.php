<?php

use App\Services\AI\HttpCareerAiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

test('HTTP career AI client returns successful JSON response', function () {
    config([
        'career-ai.base_url' => 'https://career-ai.test',
        'career-ai.endpoint' => '/api/v1/recommendations',
        'career-ai.api_key' => 'test-key',
        'career-ai.timeout' => 30,
        'career-ai.connect_timeout' => 10,
    ]);

    Http::fake([
        '*' => Http::response([
            'schema_version' => '1.0',
            'status' => 'completed',
            'recommendations' => [
                [
                    'biicf_career_id' => 1,
                    'rank' => 1,
                    'match_score' => 88,
                    'matched_skills' => [
                        'Programming',
                    ],
                    'skill_gaps' => [],
                    'development_plan' => [
                        'Build more software projects.',
                    ],
                    'career_readiness_score' => 76,
                    'explanation' => 'Test response.',
                ],
            ],
        ], 200),
    ]);

    $client = new HttpCareerAiClient();

    $response = $client->recommend([
        'student_profile' => [
            'programme' => 'Application Development',
        ],
    ]);

    expect($response['status'])
        ->toBe('completed')
        ->and(
            $response['recommendations'][0]['biicf_career_id']
        )
        ->toBe(1);

    Http::assertSent(function (Request $request) {
        return $request->url()
            === 'https://career-ai.test/api/v1/recommendations'
            && $request->hasHeader(
                'Authorization',
                'Bearer test-key'
            )
            && $request->hasHeader(
                'Accept',
                'application/json'
            )
            && $request['student_profile']['programme']
            === 'Application Development';
    });
});

test('HTTP career AI client works without an API key', function () {
    config([
        'career-ai.base_url' => 'https://career-ai.test',
        'career-ai.endpoint' => '/api/v1/recommendations',
        'career-ai.api_key' => null,
        'career-ai.timeout' => 30,
        'career-ai.connect_timeout' => 10,
    ]);

    Http::fake([
        '*' => Http::response([
            'schema_version' => '1.0',
            'status' => 'completed',
            'recommendations' => [],
        ], 200),
    ]);

    $client = new HttpCareerAiClient();

    $client->recommend([
        'student_profile' => [],
    ]);

    Http::assertSent(function (Request $request) {
        return ! $request->hasHeader(
            'Authorization'
        );
    });
});

test('HTTP career AI client throws for unsuccessful response', function () {
    config([
        'career-ai.base_url' => 'https://career-ai.test',
        'career-ai.endpoint' => '/api/v1/recommendations',
        'career-ai.timeout' => 30,
        'career-ai.connect_timeout' => 10,
    ]);

    Http::fake([
        '*' => Http::response([
            'message' => 'Server error',
        ], 500),
    ]);

    $client = new HttpCareerAiClient();

    expect(
        fn () => $client->recommend([
            'student_profile' => [],
        ])
    )->toThrow(
        RequestException::class
    );
});

test('HTTP career AI client requires a base URL', function () {
    config([
        'career-ai.base_url' => null,
        'career-ai.endpoint' => '/api/v1/recommendations',
    ]);

    $client = new HttpCareerAiClient();

    expect(
        fn () => $client->recommend([
            'student_profile' => [],
        ])
    )->toThrow(
        RuntimeException::class,
        'Career AI base URL is not configured.'
    );
});

test('HTTP career AI client requires an endpoint', function () {
    config([
        'career-ai.base_url' => 'https://career-ai.test',
        'career-ai.endpoint' => null,
    ]);

    $client = new HttpCareerAiClient();

    expect(
        fn () => $client->recommend([
            'student_profile' => [],
        ])
    )->toThrow(
        RuntimeException::class,
        'Career AI endpoint is not configured.'
    );
});

test('HTTP career AI client rejects invalid JSON response', function () {
    config([
        'career-ai.base_url' => 'https://career-ai.test',
        'career-ai.endpoint' => '/api/v1/recommendations',
        'career-ai.timeout' => 30,
        'career-ai.connect_timeout' => 10,
    ]);

    Http::fake([
        '*' => Http::response(
            'not-valid-json',
            200,
            [
                'Content-Type' => 'application/json',
            ]
        ),
    ]);

    $client = new HttpCareerAiClient();

    expect(
        fn () => $client->recommend([
            'student_profile' => [],
        ])
    )->toThrow(
        UnexpectedValueException::class,
        'Career AI API returned an invalid JSON response.'
    );
});