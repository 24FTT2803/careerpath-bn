<?php

namespace App\Services\CpbnBot;

use App\Contracts\CpbnBotClient;
use App\Models\CpbnBotConversation;
use App\Models\CpbnBotMessage;
use App\Models\User;
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
        private CpbnBotLinks $linkResolver,
    ) {}

    /**
     * Ask the CPBN Bot a question in the given conversation.
     *
     * A new conversation is passed unsaved and saved only once it
     * has an exchange to hold, so a failed request leaves nothing
     * behind.
     *
     * @return array{message: string, links: array<int, array<string, mixed>>, navigate: array<string, mixed>|null, tokens: int, conversation: CpbnBotConversation}
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
            'navigate' => ['nullable'],
        ])->validate();

        /*
         * Where to take the student straight away, if they asked
         * to go somewhere. Never a plain trip to the page they are
         * on, which would only reload it.
         */
        $navigate = $this->destination($validated['navigate'] ?? null, $currentPage);

        /*
         * Only links that resolve to something real, and never a
         * plain link to the page the student is already on: a
         * button back to where they are is noise. A link deeper
         * into the page they are on (a role's competencies, say)
         * is kept.
         */
        $links = collect($validated['links'] ?? [])
            ->filter(fn ($link) => is_string($link) || is_array($link))
            ->map(fn ($link) => $this->linkResolver->normalise($link))
            ->filter()
            ->reject(fn (array $link) => $this->linkResolver->isOnlyThePage($link, $currentPage))
            ->unique(fn (array $link) => json_encode($link))
            ->take(2)
            ->values()
            ->all();

        /*
         * Kept as the reply's first button too, so the way there is
         * still in the conversation when it is reopened later.
         */
        if ($navigate !== null) {
            $links = collect([$navigate])
                ->merge($links)
                ->unique(fn (array $link) => json_encode($link))
                ->take(2)
                ->values()
                ->all();
        }

        $tokens = $this->tokensUsed($response, $context, $history, $question, $validated['message']);

        $this->recordExchange($conversation, $question, $validated['message'], $links, $tokens);

        return [
            'message' => $validated['message'],
            'links' => $links,
            'navigate' => $navigate,
            'tokens' => $tokens,
            'conversation' => $conversation,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function destination(mixed $navigate, ?string $currentPage): ?array
    {
        if (! is_string($navigate) && ! is_array($navigate)) {
            return null;
        }

        if (is_array($navigate) && blank($navigate['page'] ?? null)) {
            return null;
        }

        $place = $this->linkResolver->normalise($navigate);

        if ($place === null || $this->linkResolver->isOnlyThePage($place, $currentPage)) {
            return null;
        }

        return $place;
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
     * @param  array<int, array<string, mixed>>  $links
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
