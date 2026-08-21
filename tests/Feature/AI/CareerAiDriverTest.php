<?php

use App\Contracts\CareerAiClient;
use App\Services\AI\HttpCareerAiClient;
use App\Services\AI\MockCareerAiClient;

test('mock driver resolves the mock career AI client', function () {
    config([
        'career-ai.driver' => 'mock',
    ]);

    $client = app(
        CareerAiClient::class
    );

    expect($client)
        ->toBeInstanceOf(
            MockCareerAiClient::class
        );
});

test('HTTP driver resolves the HTTP career AI client', function () {
    config([
        'career-ai.driver' => 'http',
    ]);

    $client = app(
        CareerAiClient::class
    );

    expect($client)
        ->toBeInstanceOf(
            HttpCareerAiClient::class
        );
});

test('unsupported career AI driver is rejected', function () {
    config([
        'career-ai.driver' => 'unsupported',
    ]);

    expect(
        fn () => app(
            CareerAiClient::class
        )
    )->toThrow(
        \RuntimeException::class,
        'Unsupported Career AI driver: unsupported'
    );
});