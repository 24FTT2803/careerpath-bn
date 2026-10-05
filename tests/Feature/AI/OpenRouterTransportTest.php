<?php

use App\Contracts\ChatTransport;
use App\Services\AI\GroqClient;
use App\Services\AI\OpenRouterClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'career-ai.transport' => 'openrouter',
        'career-ai.openrouter.base_url' => 'https://example.test/api/v1',
        'career-ai.openrouter.endpoint' => '/chat/completions',
        'career-ai.openrouter.api_key' => 'test-key',
        'career-ai.openrouter.model' => 'openai/gpt-oss-20b',
    ]);
});

function fakeOpenRouterReply(): void
{
    Http::fake([
        '*' => Http::response([
            'model' => 'openai/gpt-oss-20b',

            'choices' => [
                ['message' => ['content' => '{"ok":true}']],
            ],
        ]),
    ]);
}

function probeSchema(): array
{
    return [
        'type' => 'json_schema',

        'json_schema' => [
            'name' => 'probe',
            'strict' => true,

            'schema' => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['ok'],
                'properties' => ['ok' => ['type' => 'boolean']],
            ],
        ],
    ];
}

test('the configured transport decides who is asked', function () {
    expect(app(ChatTransport::class))
        ->toBeInstanceOf(OpenRouterClient::class);

    config(['career-ai.transport' => 'groq']);

    /*
     * The switch back has to work on its own, because it is what
     * makes trying a provider safe.
     */
    expect(app(ChatTransport::class))
        ->toBeInstanceOf(GroqClient::class);
});

test(
    'a schema request is pinned to upstreams that honour it',
    function () {
        fakeOpenRouterReply();

        app(OpenRouterClient::class)->chat(
            [['role' => 'user', 'content' => 'Hello.']],
            probeSchema()
        );

        /*
         * One model is served by many upstreams and they do not
         * all honour response_format. The career clients throw on
         * a reply that misses their schema, so an upstream that
         * ignores it fails rather than degrades.
         */
        Http::assertSent(function ($request) {
            return $request['provider']['require_parameters'] === true;
        });
    }
);

test(
    'a free-form request is not pinned',
    function () {
        fakeOpenRouterReply();

        app(OpenRouterClient::class)->chat(
            [['role' => 'user', 'content' => 'Hello.']]
        );

        /*
         * Narrowing the pool costs availability, and there is
         * nothing to conform to when no schema was asked for.
         */
        Http::assertSent(function ($request) {
            return ! isset($request['provider']['require_parameters']);
        });
    }
);

test(
    'reasoning effort is left out unless it is configured',
    function () {
        fakeOpenRouterReply();

        app(OpenRouterClient::class)->chat(
            [['role' => 'user', 'content' => 'Hello.']]
        );

        /*
         * reasoning_effort belongs to the GPT-OSS family. An
         * upstream that does not recognise it may reject the whole
         * request rather than ignore the parameter.
         */
        Http::assertSent(function ($request) {
            return ! array_key_exists(
                'reasoning_effort',
                $request->data()
            );
        });
    }
);

test(
    'an error reported in a successful response still fails',
    function () {
        Http::fake([
            '*' => Http::response([
                'error' => ['message' => 'No endpoints found.'],
            ]),
        ]);

        /*
         * A router reports upstream failures in the body with a
         * 200, so nothing would raise without this check and the
         * caller would see an empty answer instead.
         */
        expect(fn () => app(OpenRouterClient::class)->chat(
            [['role' => 'user', 'content' => 'Hello.']]
        ))->toThrow(
            UnexpectedValueException::class,
            'No endpoints found.'
        );
    }
);

test('a missing API key is reported plainly', function () {
    config(['career-ai.openrouter.api_key' => '']);

    expect(fn () => app(OpenRouterClient::class)->chat(
        [['role' => 'user', 'content' => 'Hello.']]
    ))->toThrow(
        RuntimeException::class,
        'OpenRouter API key is not configured.'
    );
});
