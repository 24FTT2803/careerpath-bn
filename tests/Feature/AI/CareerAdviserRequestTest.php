<?php

use App\Services\AI\GroqCareerAdviserClient;
use App\Services\AI\GroqClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'career-ai.groq.base_url' => 'https://example.test',
        'career-ai.groq.endpoint' => '/chat/completions',
        'career-ai.groq.api_key' => 'test-key',
        'career-ai.groq.model' => 'test-model',
    ]);
});

function fakeGroqReply(): void
{
    Http::fake([
        '*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'schema_version' => '1.0',
                            'status' => 'completed',
                            'message' => 'An answer.',
                        ]),
                    ],
                ],
            ],
        ]),
    ]);
}

test(
    'the adviser asks for a low temperature',
    function () {
        fakeGroqReply();

        app(GroqCareerAdviserClient::class)->ask(
            ['profile' => []],
            'What should I work on?'
        );

        /*
         * Nothing was sent before, so the adviser ran at
         * whatever the provider defaults to. Competency names
         * and levels are defined terms and should not be
         * paraphrased.
         */
        Http::assertSent(function ($request) {
            return $request['temperature'] === 0.5;
        });
    }
);

test(
    'no temperature is sent when none is configured',
    function () {
        fakeGroqReply();

        config(['career-ai.groq.temperature' => null]);

        app(GroqClient::class)->chat([
            ['role' => 'user', 'content' => 'Hello.'],
        ]);

        /*
         * Left out entirely rather than guessed at, so the
         * provider's own default still applies.
         */
        Http::assertSent(function ($request) {
            return ! array_key_exists(
                'temperature',
                $request->data()
            );
        });
    }
);

test(
    'a configured temperature applies to every request',
    function () {
        fakeGroqReply();

        config(['career-ai.groq.temperature' => 0.2]);

        app(GroqClient::class)->chat([
            ['role' => 'user', 'content' => 'Hello.'],
        ]);

        Http::assertSent(function ($request) {
            return $request['temperature'] === 0.2;
        });
    }
);

test(
    'the adviser is told to answer rather than refuse',
    function () {
        fakeGroqReply();

        app(GroqCareerAdviserClient::class)->ask(
            ['profile' => []],
            'What films should I watch?'
        );

        Http::assertSent(function ($request) {
            $prompt = $request['messages'][0]['content'];

            /*
             * A question phrased unexpectedly is still usually a
             * question about building a skill, and refusing it
             * helps nobody.
             */
            return str_contains(
                $prompt,
                'Do not refuse a question because it is phrased unexpectedly'
            );
        });
    }
);

test(
    'the adviser is told to say what the data does not cover',
    function () {
        fakeGroqReply();

        app(GroqCareerAdviserClient::class)->ask(
            ['profile' => []],
            'Which certification should I take?'
        );

        Http::assertSent(function ($request) {
            $prompt = $request['messages'][0]['content'];

            /*
             * Naming a certification nobody supplied is the
             * failure this exists to prevent.
             */
            return str_contains(
                $prompt,
                'unless that exact name appears in the supplied context'
            )
                && str_contains(
                    $prompt,
                    'does not yet hold training recommendations'
                );
        });
    }
);
