<?php

namespace App\Services\CpbnBot;

use App\Contracts\CpbnBotClient;
use App\Models\CpbnBotConversation;
use App\Models\CpbnBotMessage;
use App\Models\User;
use App\Support\CpbnBotKnowledge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CpbnBotService
{
    /**
     * How many earlier messages are replayed, so a follow-up such
     * as "and then?" is understood.
     */
    public const HISTORY_LIMIT = 10;

    /**
     * Roughly what the bot's instructions cost, added to an
     * estimate when the provider does not report its own count.
     */
    public const PROMPT_TOKEN_ALLOWANCE = 800;

    public function __construct(
        private CpbnBotClient $client,
        private CpbnBotContextBuilder $contextBuilder,
    ) {}

    /**
     * Ask the CPBN Bot a question in the given conversation.
     *
     * A new conversation is passed unsaved and saved only once it
     * has an exchange to hold, so a failed request leaves nothing
     * behind.
     *
     * @return array{message: string, links: array<int, string>, tokens: int, conversation: CpbnBotConversation}
     */
    public function ask(
        User $student,
        string $message,
        CpbnBotConversation $conversation,
        ?string $currentPage = null
    ): array {
        $question = trim($message);
        $context = $this->contextBuilder->build($student, $currentPage);
        $history = $conversation->exists ? $this->recentHistory($conversation) : [];

        $response = $this->client->ask($context, $history, $question);

        $validated = Validator::make($response, [
            'status' => ['required', 'in:completed'],
            'message' => ['required', 'string', 'max:5000'],
            'links' => ['nullable', 'array'],
        ])->validate();

        /*
         * Only pages the bot knows, and never the page the student
         * is already on: a button back to where they are is noise.
         */
        $links = collect($validated['links'] ?? [])
            ->filter(fn ($key) => is_string($key) && in_array($key, CpbnBotKnowledge::pageKeys(), true))
            ->reject(fn (string $key) => $key === $currentPage)
            ->unique()
            ->take(2)
            ->values()
            ->all();

        $tokens = $this->tokensUsed($response, $context, $history, $question, $validated['message']);

        $this->recordExchange($conversation, $question, $validated['message'], $links, $tokens);

        return [
            'message' => $validated['message'],
            'links' => $links,
            'tokens' => $tokens,
            'conversation' => $conversation,
        ];
    }

    /**
     * The student's current conversation, the one most recently
     * written in, or an unsaved new one.
     */
    public function currentConversation(User $student): CpbnBotConversation
    {
        return $student->cpbnBotConversations()
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->first()
            ?? $this->newConversation($student);
    }

    public function newConversation(User $student): CpbnBotConversation
    {
        return new CpbnBotConversation(['user_id' => $student->id]);
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function recentHistory(CpbnBotConversation $conversation): array
    {
        return $conversation->messages()
            ->reorder()
            ->latest('id')
            ->limit(self::HISTORY_LIMIT)
            ->get()
            ->sortBy('id')
            ->map(fn (CpbnBotMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();
    }

    /**
     * The provider's own count where it gives one, otherwise an
     * estimate of about four characters a token.
     *
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $context
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function tokensUsed(array $response, array $context, array $history, string $question, string $answer): int
    {
        $reported = $response['usage']['total_tokens'] ?? null;

        if (is_numeric($reported) && (int) $reported > 0) {
            return (int) $reported;
        }

        $characters = strlen((string) json_encode($context))
            + strlen((string) json_encode($history))
            + strlen($question)
            + strlen($answer);

        return self::PROMPT_TOKEN_ALLOWANCE + (int) ceil($characters / 4);
    }

    /**
     * @param  array<int, string>  $links
     */
    private function recordExchange(
        CpbnBotConversation $conversation,
        string $question,
        string $answer,
        array $links,
        int $tokens
    ): void {
        DB::transaction(function () use ($conversation, $question, $answer, $links, $tokens): void {
            if (! $conversation->exists) {
                /* Titled after its first question, so a list of past
                 * conversations says what each was about. */
                $conversation->forceFill([
                    'title' => Str::limit($question, 60),
                    'message_count' => 0,
                    'last_message_at' => now(),
                ])->save();
            }

            $conversation->messages()->create([
                'role' => CpbnBotMessage::ROLE_USER,
                'content' => $question,
            ]);

            $conversation->messages()->create([
                'role' => CpbnBotMessage::ROLE_ASSISTANT,
                'content' => $answer,
                'links' => $links,
                'tokens' => $tokens,
            ]);

            $conversation->forceFill([
                'message_count' => $conversation->message_count + 2,
                'last_message_at' => now(),
            ])->save();
        });
    }
}
