<?php

use App\Services\AI\HttpCareerAiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

test('HTTP career AI client returns successful JSON response', function () {
    config([
        'career-ai.base_url' => 'https://career-ai.test',
        'career-ai.endpoint' => '/api/v1/recommendations',
        'career-ai.api_key' => 'test-key',
        'career-ai.timeout' => 30,
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
                    'matched_skills' => ['Programming'],
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
        ->and($response['recommendations'][0]['biicf_career_id'])
        ->toBe(1);

    Http::assertSent(function (Request $request) {
        return $request->url()
            === 'https://career-ai.test/api/v1/recommendations'
            && $request->hasHeader(
                'Authorization',
                'Bearer test-key'
            )
            && $request['student_profile']['programme']
            === 'Application Development';
    });
});

test('HTTP career AI client throws for unsuccessful response', function () {
    config([
        'career-ai.base_url' => 'https://career-ai.test',
        'career-ai.endpoint' => '/api/v1/recommendations',
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
    )->toThrow(RequestException::class);
});

test('HTTP career AI client requires a base URL', function () {
    config([
        'career-ai.base_url' => null,
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