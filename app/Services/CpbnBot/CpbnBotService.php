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
     * Earlier messages replayed outside a profile-filling session:
     * enough for a follow-up such as "and then?", and no more, so
     * an ordinary question stays cheap.
     */
    public const HISTORY_LIMIT = 6;

    /**
     * The most messages a single filling session replays, as a
     * safeguard. A session normally ends long before this.
     */
    public const SESSION_LIMIT = 80;

    /**
     * Roughly what the bot's instructions cost, added to an
     * estimate when the provider does not report its own count.
     */
    public const PROMPT_TOKEN_ALLOWANCE = 800;

    public function __construct(
        private CpbnBotClient $client,
        private CpbnBotContextBuilder $contextBuilder,
        private CpbnBotLinks $linkResolver,
        private CpbnBotProfileFill $profileFill,
    ) {}

    /**
     * Ask the CPBN Bot a question in the given conversation.
     *
     * A new conversation is passed unsaved and saved only once it
     * has an exchange to hold, so a failed request leaves nothing
     * behind.
     *
     * @return array{message: string, links: array<int, array<string, mixed>>, navigate: array<string, mixed>|null, choices: array<string, mixed>|null, fill: array<string, mixed>|null, tokens: int, conversation: CpbnBotConversation}
     */
    public function ask(
        User $student,
        string $message,
        CpbnBotConversation $conversation,
        ?string $currentPage = null,
        ?array $formState = null
    ): array {
        $question = trim($message);
        $history = $conversation->exists ? $this->history($conversation) : [];

        /*
         * The question and the student's last two messages, so a
         * follow-up such as "take me there" still finds the role
         * the conversation is about.
         */
        $recentText = collect($history)
            ->where('role', CpbnBotMessage::ROLE_USER)
            ->pluck('content')
            ->take(-2)
            ->push($question)
            ->values()
            ->all();

        $context = $this->contextBuilder->build($student, $currentPage, $recentText);

        /*
         * The section being filled in, and what the form on the page
         * holds for it right now, read from the page rather than
         * pieced together from the chat.
         */
        $context['fill_session'] = [
            'section' => $conversation->fill_section,
            'form_now' => $formState,
        ];

        $response = $this->client->ask($context, $history, $question);

        $validated = Validator::make($response, [
            'status' => ['required', 'in:completed'],
            'message' => ['required', 'string', 'max:5000'],
            'links' => ['nullable', 'array'],
            'navigate' => ['nullable'],
            'choices' => ['nullable'],
            'fill' => ['nullable'],
            'fill_section' => ['nullable', 'string'],
        ])->validate();

        $fillSection = $validated['fill_section'] ?? '';

        /*
         * Asked outright to fill a section ("Help me fill in my
         * Projects & Experience", as the '?' guides send), the
         * session starts even if the bot did not say so.
         */
        if ($fillSection === '' && ! $conversation->fill_section) {
            $fillSection = $this->sectionAskedFor($question) ?? '';
        }

        $choices = $this->choices($validated['choices'] ?? null);
        $fill = $this->profileFill->normalise($validated['fill'] ?? null);

        /*
         * Filling happens on Edit Profile. Asked from anywhere else,
         * the student is taken there and the form is filled on
         * arrival.
         */
        if ($fill !== null && $currentPage !== 'profile_edit') {
            $validated['navigate'] = 'profile_edit';
        }

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

        $this->recordExchange($conversation, $question, $validated['message'], $links, $tokens, $fillSection);

        return [
            'message' => $validated['message'],
            'links' => $links,
            'navigate' => $navigate,
            'choices' => $choices,
            'fill' => $fill,
            'fill_section' => $conversation->fill_section,
            'tokens' => $tokens,
            'conversation' => $conversation,
        ];
    }

    /**
     * Answers the student can pick instead of typing.
     *
     * @return array{options: array<int, string>, multiple: bool}|null
     */
    private function choices(mixed $choices): ?array
    {
        if (! is_array($choices)) {
            return null;
        }

        $options = collect($choices['options'] ?? [])
            ->filter(fn ($option) => is_string($option) && trim($option) !== '')
            ->map(fn (string $option) => Str::limit(trim($option), 60, ''))
            ->unique()
            ->take(12)
            ->values()
            ->all();

        return $options === []
            ? null
            : ['options' => $options, 'multiple' => (bool) ($choices['multiple'] ?? false)];
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
     * What the bot is shown of the conversation so far.
     *
     * While a profile section is being filled in, everything said
     * since that began, so no answer is forgotten however long it
     * takes. Otherwise only the last few messages.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function history(CpbnBotConversation $conversation): array
    {
        $query = $conversation->messages()->reorder();

        if ($conversation->fill_section && $conversation->fill_started_message_id) {
            $query->where('id', '>=', $conversation->fill_started_message_id)
                ->orderBy('id')
                ->limit(self::SESSION_LIMIT);
        } else {
            $query->latest('id')->limit(self::HISTORY_LIMIT);
        }

        return $query->get()
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
        int $tokens,
        string $fillSection = ''
    ): void {
        DB::transaction(function () use ($conversation, $question, $answer, $links, $tokens, $fillSection): void {
            if (! $conversation->exists) {
                /* Titled after its first question, so a list of past
                 * conversations says what each was about. */
                $conversation->forceFill([
                    'title' => Str::limit($question, 60),
                    'message_count' => 0,
                    'last_message_at' => now(),
                ])->save();
            }

            $asked = $conversation->messages()->create([
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
            ] + $this->sessionAfter($conversation, $fillSection, $asked->id))->save();
        });
    }

    /**
     * The profile section a request to fill one names, if any.
     */
    private function sectionAskedFor(string $question): ?string
    {
        $text = Str::lower($question);

        if (! Str::contains($text, ['fill', 'complete', 'update', 'edit'])) {
            return null;
        }

        $words = [
            'personal' => ['personal', 'phone', 'date of birth', 'nationality'],
            'qualifications' => ['qualification'],
            'skills' => ['skill', 'competenc'],
            'interests' => ['interest'],
            'projects' => ['project', 'experience'],
            'certifications' => ['certification', 'certificate'],
            'aspirations' => ['aspiration', 'dream career', 'vision', 'long term goal', 'long-term goal'],
        ];

        foreach ($words as $section => $names) {
            if (Str::contains($text, $names)) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Where the filling session stands after this reply.
     *
     * A section the bot says it is working on starts a session at
     * this question, unless that section's session is already
     * running. "done", which the bot says only once the student has
     * confirmed the section is finished, ends it. Anything else,
     * such as a question about something unrelated partway through,
     * leaves it as it was.
     *
     * @return array<string, mixed>
     */
    private function sessionAfter(CpbnBotConversation $conversation, string $fillSection, int $questionId): array
    {
        if ($fillSection === 'done') {
            return ['fill_section' => null, 'fill_started_message_id' => null];
        }

        if (array_key_exists($fillSection, CpbnBotProfileFill::sections())
            && $fillSection !== $conversation->fill_section) {
            return ['fill_section' => $fillSection, 'fill_started_message_id' => $questionId];
        }

        return [];
    }
}
