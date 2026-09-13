<?php

namespace App\Services\AI;

use App\Contracts\CareerAdviserClient;
use App\Models\CareerAdviserConversation;
use App\Models\CareerAdviserMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CareerAdviserService
{
    public function __construct(
        private CareerAdviserClient $careerAdviser,
        private CareerAdviserContextBuilder $contextBuilder
    ) {}

    /**
     * How many earlier messages are replayed to the adviser.
     *
     * Enough for the student to say "why?" and be understood,
     * without the prompt growing without limit as the thread
     * accumulates over months.
     */
    public const HISTORY_LIMIT = 10;

    /**
     * Ask the Career Adviser a question using the
     * student's current CareerPath context.
     *
     * The exchange is stored only after the adviser has
     * returned a valid response, so a failed request never
     * leaves an unanswered question sitting in the thread.
     */
    public function ask(
        User $student,
        string $message,
        bool $includeHistory = true
    ): array {
        $conversation = $this->conversationFor(
            $student
        );

        $question = trim($message);

        $context = $this->contextBuilder->build(
            $student
        );

        if ($includeHistory) {
            $context['conversation'] = $this->recentHistory(
                $conversation
            );
        }

        $response = $this->careerAdviser->ask(
            $context,
            $question
        );

        $validated = $this->validateResponse(
            $response
        );

        $this->recordExchange(
            $student,
            $conversation,
            $question,
            $validated['message']
        );

        return $validated;
    }

    /**
     * Get the student's running thread, starting one if this is
     * their first question.
     */
    public function conversationFor(
        User $student
    ): CareerAdviserConversation {
        return CareerAdviserConversation::firstOrCreate([
            'user_id' => $student->id,
        ]);
    }

    /**
     * The tail of the thread, oldest first, in the shape the
     * adviser context expects.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function recentHistory(
        CareerAdviserConversation $conversation
    ): array {
        return $conversation
            ->messages()
            ->latest('id')
            ->limit(self::HISTORY_LIMIT)
            ->get()
            ->sortBy('id')
            ->map(fn (CareerAdviserMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();
    }

    /**
     * Persist a question and its answer as one exchange.
     */
    private function recordExchange(
        User $student,
        CareerAdviserConversation $conversation,
        string $question,
        string $answer
    ): void {
        /*
         * Recorded per message so a question asked months later
         * can still be traced to the recommendations the student
         * was looking at when they asked it.
         */
        $generationId = $student
            ->currentRecommendationGeneration()
            ->first()
            ?->id;

        DB::transaction(function () use (
            $conversation,
            $generationId,
            $question,
            $answer
        ): void {
            $conversation->messages()->create([
                'recommendation_generation_id' => $generationId,
                'role' => CareerAdviserMessage::ROLE_USER,
                'content' => $question,
            ]);

            $conversation->messages()->create([
                'recommendation_generation_id' => $generationId,
                'role' => CareerAdviserMessage::ROLE_ASSISTANT,
                'content' => $answer,
            ]);

            $conversation->forceFill([
                'message_count' => $conversation->message_count + 2,
                'last_message_at' => now(),
            ])->save();
        });
    }

    /**
     * Validate the structured response returned by
     * the configured Career Adviser client.
     */
    private function validateResponse(
        array $response
    ): array {
        return Validator::make(
            $response,
            [
                'schema_version' => [
                    'required',
                    'string',
                ],

                'status' => [
                    'required',
                    'in:completed',
                ],

                'message' => [
                    'required',
                    'string',
                    'max:5000',
                ],
            ]
        )->validate();
    }
}
