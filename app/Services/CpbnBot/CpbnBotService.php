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
     * The most messages one part of a conversation replays, as a
     * safeguard against a part that never ends. A filling session
     * normally finishes long before this.
     */
    public const MEMORY_LIMIT = 80;

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
        ?array $formState = null,
        ?string $startSection = null
    ): array {
        $question = trim($message);
        $active = $conversation->fill_section;

        /*
         * A section asked for outright starts a session here, and
         * memory with it: the '?' guides name the section, and a
         * typed "help me fill in my projects" is recognised. Nothing
         * said before it is shown to the bot.
         */
        $asked = array_key_exists((string) $startSection, CpbnBotProfileFill::sections())
            ? $startSection
            : $this->sectionAskedFor($question);

        $startsNow = $asked !== null && $asked !== $active;

        $history = $conversation->exists && ! $startsNow ? $this->history($conversation) : [];

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
            'section' => $startsNow ? $asked : $active,
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

        $memory = $this->memoryChanges(
            $active,
            $startsNow ? $asked : null,
            (string) ($validated['fill_section'] ?? '')
        );

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

        $this->recordExchange($conversation, $question, $validated['message'], $links, $tokens, $memory);

        return [
            'message' => $validated['message'],
            'links' => $links,
            'navigate' => $navigate,
            'choices' => $choices,
            'fill' => $fill,
            'fill_section' => $conversation->fill_section,
            'memory' => [
                'before' => $memory['before'],
                'after' => $memory['after'],
            ],
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
     * What the bot is shown of the conversation so far: everything
     * since its memory last began, and nothing before.
     *
     * Memory begins when a section starts being filled in, and
     * begins afresh once that section is finished. The student sees
     * a marker in the chat at each of those points, so it is always
     * plain what the bot remembers. Never a count of recent
     * messages, which would forget the start of a long session and
     * remember an unrelated one.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function history(CpbnBotConversation $conversation): array
    {
        return $conversation->messages()
            ->reorder()
            ->where('role', '!=', CpbnBotMessage::ROLE_DIVIDER)
            ->when(
                $conversation->memory_from_message_id,
                fn ($query, $from) => $query->where('id', '>=', $from)
            )
            ->latest('id')
            ->limit(self::MEMORY_LIMIT)
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
     * How this exchange moves the conversation's memory.
     *
     * A session starts when the student asks for a section, or when
     * the bot takes one up; switching sections ends the one before.
     * "done", which the bot sends only once the student has
     * confirmed the section is finished, ends it after this reply.
     * Each start and end is a marker in the conversation.
     *
     * @return array{before: array<int, array<string, string>>, after: array<int, array<string, string>>, section: string|null}
     */
    private function memoryChanges(?string $active, ?string $asked, string $declared): array
    {
        $sections = CpbnBotProfileFill::sections();

        $start = $asked
            ?? (array_key_exists($declared, $sections) && $declared !== $active ? $declared : null);

        $before = [];

        if ($start !== null) {
            if ($active !== null && isset($sections[$active])) {
                $before[] = $this->marker('end', $active);
            }

            $before[] = $this->marker('start', $start);
        }

        $section = $start ?? $active;
        $after = [];

        if ($declared === 'done' && $section !== null && isset($sections[$section])) {
            $after[] = $this->marker('end', $section);
            $section = null;
        }

        return ['before' => $before, 'after' => $after, 'section' => $section];
    }

    /**
     * @return array{type: string, section: string, title: string}
     */
    private function marker(string $type, string $section): array
    {
        return [
            'type' => $type,
            'section' => $section,
            'title' => CpbnBotProfileFill::sections()[$section]['title'],
        ];
    }

    /**
     * Finish the section being filled in without asking the bot, and
     * let go of what was said about it.
     *
     * @return array{type: string, section: string, title: string}|null
     */
    public function finishSection(CpbnBotConversation $conversation): ?array
    {
        $section = $conversation->fill_section;

        if ($section === null || ! isset(CpbnBotProfileFill::sections()[$section])) {
            return null;
        }

        $marker = $this->marker('end', $section);

        DB::transaction(function () use ($conversation, $marker): void {
            $divider = $this->recordMarker($conversation, $marker);

            $conversation->forceFill([
                'fill_section' => null,
                'memory_from_message_id' => $divider->id,
            ])->save();
        });

        return $marker;
    }

    /**
     * @param  array<string, string>  $marker
     */
    private function recordMarker(CpbnBotConversation $conversation, array $marker): CpbnBotMessage
    {
        return $conversation->messages()->create([
            'role' => CpbnBotMessage::ROLE_DIVIDER,
            'content' => json_encode($marker),
        ]);
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
     * @param  array{before: array<int, array<string, string>>, after: array<int, array<string, string>>, section: string|null}  $memory
     */
    private function recordExchange(
        CpbnBotConversation $conversation,
        string $question,
        string $answer,
        array $links,
        int $tokens,
        array $memory
    ): void {
        DB::transaction(function () use ($conversation, $question, $answer, $links, $tokens, $memory): void {
            if (! $conversation->exists) {
                /* Titled after its first question, so a list of past
                 * conversations says what each was about. */
                $conversation->forceFill([
                    'title' => Str::limit($question, 60),
                    'message_count' => 0,
                    'last_message_at' => now(),
                ])->save();
            }

            $memoryFrom = $conversation->memory_from_message_id;

            /* A session starting here: its marker comes first, and memory begins at it. */
            foreach ($memory['before'] as $marker) {
                $divider = $this->recordMarker($conversation, $marker);

                if ($marker['type'] === 'start') {
                    $memoryFrom = $divider->id;
                }
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

            /* A section finished: memory begins again after its marker. */
            foreach ($memory['after'] as $marker) {
                $memoryFrom = $this->recordMarker($conversation, $marker)->id;
            }

            $conversation->forceFill([
                'message_count' => $conversation->message_count + 2,
                'last_message_at' => now(),
                'fill_section' => $memory['section'],
                'memory_from_message_id' => $memoryFrom,
            ])->save();
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
}
