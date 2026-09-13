<?php

use App\Contracts\CareerAdviserClient;
use App\Models\CareerAdviserConversation;
use App\Models\CareerAdviserMessage;
use App\Models\RecommendationGeneration;
use App\Models\User;
use App\Services\AI\CareerAdviserContextBuilder;
use App\Services\AI\CareerAdviserService;
use App\Services\AI\CareerAiPayloadBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * An adviser client that answers predictably and records the
 * context it was handed, so tests can assert what the AI saw.
 */
function recordingAdviserClient(
    string $reply = 'Here is some career guidance.'
): CareerAdviserClient {
    return new class($reply) implements CareerAdviserClient
    {
        /** @var array<string, mixed> */
        public array $lastContext = [];

        public function __construct(
            private string $reply
        ) {}

        public function ask(
            array $context,
            string $message
        ): array {
            $this->lastContext = $context;

            return [
                'schema_version' => '1.0',
                'status' => 'completed',
                'message' => $this->reply,
            ];
        }
    };
}

function failingAdviserClient(): CareerAdviserClient
{
    return new class implements CareerAdviserClient
    {
        public function ask(
            array $context,
            string $message
        ): array {
            throw new RuntimeException(
                'Adviser unavailable.'
            );
        }
    };
}

function makeCareerAdviserService(
    CareerAdviserClient $client
): CareerAdviserService {
    $payloadBuilder = new CareerAiPayloadBuilder;

    return new CareerAdviserService(
        $client,
        new CareerAdviserContextBuilder(
            $payloadBuilder
        )
    );
}

test(
    'asking stores the question and the answer',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        makeCareerAdviserService(
            recordingAdviserClient('You should learn PHP.')
        )->ask($student, '  What should I learn?  ');

        $conversation = $student
            ->fresh()
            ->careerAdviserConversation;

        expect($conversation)->not->toBeNull();

        $messages = $conversation->messages()->get();

        expect($messages)->toHaveCount(2);

        expect($messages[0]->role)
            ->toBe(CareerAdviserMessage::ROLE_USER)
            ->and($messages[0]->content)
            ->toBe('What should I learn?')
            ->and($messages[1]->role)
            ->toBe(CareerAdviserMessage::ROLE_ASSISTANT)
            ->and($messages[1]->content)
            ->toBe('You should learn PHP.');

        expect($conversation->message_count)
            ->toBe(2)
            ->and($conversation->last_message_at)
            ->not->toBeNull();
    }
);

test(
    'a student keeps one running thread across questions',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $service = makeCareerAdviserService(
            recordingAdviserClient()
        );

        $service->ask($student, 'First question?');
        $service->ask($student->fresh(), 'Second question?');

        expect(
            CareerAdviserConversation::where(
                'user_id',
                $student->id
            )->count()
        )->toBe(1);

        $conversation = $student
            ->fresh()
            ->careerAdviserConversation;

        expect($conversation->messages()->count())
            ->toBe(4)
            ->and($conversation->message_count)
            ->toBe(4);
    }
);

test(
    'earlier messages are replayed to the adviser',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $client = recordingAdviserClient();
        $service = makeCareerAdviserService($client);

        $service->ask($student, 'First question?');
        $service->ask($student->fresh(), 'Why?');

        $replayed = collect(
            $client->lastContext['conversation'] ?? []
        );

        /*
         * Without this the adviser cannot answer a follow-up
         * like "why?" because it never saw what came before.
         */
        expect($replayed)->toHaveCount(2);

        expect($replayed->pluck('content')->all())
            ->toContain('First question?');
    }
);

test(
    'history can be withheld from the adviser',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $client = recordingAdviserClient();
        $service = makeCareerAdviserService($client);

        $service->ask($student, 'First question?');

        $service->ask(
            $student->fresh(),
            'Second question?',
            includeHistory: false
        );

        expect($client->lastContext)
            ->not->toHaveKey('conversation');

        /*
         * Withholding history from the prompt must not stop the
         * exchange being stored.
         */
        expect(
            $student
                ->fresh()
                ->careerAdviserConversation
                ->messages()
                ->count()
        )->toBe(4);
    }
);

test(
    'messages record the generation that was active',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $generation = $student->recommendationGenerations()->create([
            'generation_number' => 1,
            'status' => RecommendationGeneration::STATUS_CURRENT,
            'driver' => 'mock',
            'recommendation_count' => 3,
            'generated_at' => now(),
        ]);

        makeCareerAdviserService(
            recordingAdviserClient()
        )->ask($student, 'Why this career?');

        $messages = $student
            ->fresh()
            ->careerAdviserConversation
            ->messages()
            ->get();

        expect($messages[0]->recommendation_generation_id)
            ->toBe($generation->id)
            ->and($messages[1]->recommendation_generation_id)
            ->toBe($generation->id);
    }
);

test(
    'a failed adviser request stores nothing',
    function () {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $service = makeCareerAdviserService(
            failingAdviserClient()
        );

        expect(
            fn () => $service->ask($student, 'Will this fail?')
        )->toThrow(RuntimeException::class);

        /*
         * A conversation row is created before the call, but an
         * unanswered question must never be left in the thread.
         */
        expect(
            $student
                ->fresh()
                ->careerAdviserConversation
                ?->messages()
                ->count() ?? 0
        )->toBe(0);
    }
);
