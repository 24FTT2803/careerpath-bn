<?php

namespace App\Services\CpbnBot;

use App\Contracts\CpbnBotClient;
use App\Models\CpbnBotConversation;
use App\Models\CpbnBotMessage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
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
        private CpbnBotSkillLevels $skillLevels,
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
        ?string $startSection = null,
        bool $confirmedFinished = false,
        ?array $formResult = null
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

            /* What the bot last asked about or filled, so "change it" means that. */
            'last_field' => $startsNow ? null : $conversation->fill_field,

            /*
             * "ok", "that's good", "nope that's good": the student is
             * happy with what was just shown and wants to carry on.
             * Said plainly, so the bot moves on instead of asking
             * again.
             */
            'student_is_moving_on' => ! $startsNow && $active !== null && $this->isMovingOn($question),
            'form_now' => $formState,

            /*
             * What the last change really did, read back from the
             * form: done, or not. So the bot knows whether the job
             * it said it would do was done, and owns up when not.
             */
            /*
             * What has been put on the form so far this section,
             * kept here as well as on the form, so nothing said is
             * asked for again, even before Edit Profile is open.
             */
            'collected' => (! $startsNow && $active !== null) ? $this->collected($conversation, $active) : [],

            'last_result' => $formResult ? [
                'done' => array_values((array) ($formResult['confirmed'] ?? [])),
                'failed' => array_values((array) ($formResult['missed'] ?? [])),
            ] : null,
        ];

        /*
         * Rating competencies is done here, not by the AI: the
         * levels are a fixed list. Whatever the student just gave
         * for the ones waiting is read now, and the bot is told.
         */
        $section = $startsNow ? $asked : $active;

        /*
         * The student's saved profile is not sent by default. While
         * a section is being filled in, only that section's saved
         * values go, with its choices (the countries for personal
         * details, the listed interests for interests). The form
         * itself, as it stands, comes with every message of the
         * session as fill_session.form_now.
         */
        $context['profile_form'] = $this->savedSection($context['profile_form'] ?? [], $section);

        if ($context['profile_form'] === null) {
            unset($context['profile_form']);
        }
        $waiting = $section === 'skills' ? $this->skillLevels->waiting($formState) : [];
        $rated = $this->skillLevels->answers($question, $waiting);
        $ratedNames = array_map(fn (array $item) => Str::lower($item['name']), $rated);

        if ($section === 'skills') {
            $context['fill_session']['skills'] = [
                'levels_set_now' => $rated,
                'still_to_rate' => array_values(array_filter(
                    $waiting,
                    fn (string $name) => ! in_array(Str::lower($name), $ratedNames, true)
                )),
                'suggested' => $this->skillLevels->suggestions(
                    $student,
                    $context['focus']['job_roles'][0]['title'] ?? null
                ),
            ];
        }

        /*
         * Whether the bot may change the page by itself. Saying
         * so in the chat ("don't take me to pages automatically")
         * changes it on the account, as the Settings toggle does,
         * and is answered here without asking the AI.
         */
        $autoNavigate = $student->cpbn_bot_auto_navigate !== false;
        $preference = $this->navigationPreference($question);

        if ($preference !== null && $preference !== $autoNavigate) {
            $student->forceFill(['cpbn_bot_auto_navigate' => $preference])->save();
            $autoNavigate = $preference;
        }

        $context['student']['bot_takes_you_to_pages'] = $autoNavigate;

        $response = $preference !== null
            ? [
                'schema_version' => '1.0',
                'status' => 'completed',
                'message' => $preference
                    ? 'Done. When you ask for something that happens on another page, I will take you there straight away.'
                    : 'Done. I will not change pages by myself any more; I will give you a link instead. You can switch this back in Settings, or just tell me.',
                'links' => [['page' => 'settings']],
                'navigate' => null,
                'choices' => null,
                'fill' => null,
                'fill_section' => '',
                'asking_field' => '',
                'usage' => ['total_tokens' => 1],
            ]
            : $this->client->ask($context, $history, $question);

        $validated = Validator::make($response, [
            'status' => ['required', 'in:completed'],
            'message' => ['required', 'string', 'max:5000'],
            'links' => ['nullable', 'array'],
            'navigate' => ['nullable'],
            'choices' => ['nullable'],
            'fill' => ['nullable'],
            'fill_section' => ['nullable', 'string'],
            'asking_field' => ['nullable', 'string'],
        ])->validate();

        $declared = (string) ($validated['fill_section'] ?? '');

        /*
         * A section ends only when the student says it is finished:
         * by pressing "Yes, that's everything", or saying so. The
         * bot deciding on its own that it is done, partway through,
         * is not enough.
         */
        if ($declared === 'done'
            && ! $confirmedFinished
            && ! $this->saysFinished($question, $conversation->fill_field === 'finish')) {
            $declared = '';
        }

        /* Said outright ("I'm done", "you can stop now"): the part ends, whatever the bot made of it. */
        if ($active !== null && ! $startsNow && $declared !== 'done' && $this->saysStop($question)) {
            $declared = 'done';
        }

        $memory = $this->memoryChanges(
            $active,
            $startsNow ? $asked : null,
            $declared
        );

        $choices = $this->choices($validated['choices'] ?? null);

        /* The levels read from the student's answer stand, whatever the bot sent for them. */
        $rawFill = is_array($validated['fill'] ?? null) ? $validated['fill'] : [];

        /* A change to the item being talked about edits it, never adds another. */
        $rawFill = $this->sameItem($rawFill, $formState, $question, $conversation);

        if ($rated !== []) {
            $rawFill['competencies'] = collect(is_array($rawFill['competencies'] ?? null) ? $rawFill['competencies'] : [])
                ->reject(fn ($item) => is_array($item) && in_array(Str::lower((string) ($item['name'] ?? '')), $ratedNames, true))
                ->merge($rated)
                ->values()
                ->all();
        }

        $fill = $this->profileFill->normalise($rawFill === [] ? ($validated['fill'] ?? null) : $rawFill);

        /*
         * During a section, a reply that sent nothing to the form
         * while the student was telling it things is backed up by a
         * second, narrow look at what they said, so their words
         * reach the boxes even when the reply forgot.
         */
        $sessionSection = $memory['section'];

        /*
         * Not on the message that opens a section ("I did a Java
         * project" is what it is about, not its title), and never
         * the field the reply itself is asking for: the form and the
         * reply would disagree.
         */
        if ($fill === null
            && ! $startsNow
            && $sessionSection !== null
            && $sessionSection !== 'skills'
            && $declared !== 'done'
            && str_word_count($question) >= 3
            && method_exists($this->client, 'extract')) {
            $extracted = $this->client->extract(
                $sessionSection,
                $startsNow ? [] : $this->collected($conversation, $sessionSection),
                $history,
                $question
            );

            $extractedFill = $this->withoutAskedField($extracted['fill'], (string) ($validated['asking_field'] ?? ''));

            if ($extractedFill !== null) {
                $fill = $this->profileFill->normalise($this->sameItem($extractedFill, $formState, $question, $conversation));
            }

            $extraTokens = (int) ($extracted['usage']['total_tokens'] ?? 0);

            if ($extraTokens > 0 && is_array($response['usage'] ?? null)) {
                $response['usage']['total_tokens'] = (int) ($response['usage']['total_tokens'] ?? 0) + $extraTokens;
            }
        }

        /* Never a change claimed before the form has confirmed it. */
        if ($declared !== 'done') {
            $validated['message'] = $this->honestAboutChanges(
                (string) $validated['message'],
                $fill,
                $currentPage,
                $autoNavigate,
                $question,
                $fill !== null && array_keys($fill) === ['milestone'] ? 'milestones' : 'profile_edit'
            );
        }

        /*
         * Then the next competency still without a level, asked
         * about here with its levels as buttons: those left from
         * before, and any just ticked.
         */
        if ($section === 'skills' && $declared !== 'done') {
            $stillWaiting = collect($waiting)
                ->reject(fn (string $name) => in_array(Str::lower($name), $ratedNames, true))
                ->merge(collect($fill['competencies'] ?? [])
                    ->whereNull('level_id')
                    ->pluck('name')
                    ->reject(fn (string $name) => $this->skillLevels->hasLevel($formState, $name)))
                ->unique(fn (string $name) => Str::lower($name))
                ->values()
                ->all();

            $next = $this->skillLevels->nextQuestion($stillWaiting);

            if ($next !== null) {
                $validated['message'] = rtrim((string) $validated['message'])."\n\n".$next['message'];
                $choices = ['options' => $next['options'], 'multiple' => false];
                $validated['asking_field'] = 'level of '.$next['name'];
            }

            /*
             * Choosing which competencies they have is done here,
             * with buttons, not in the BIICF Explorer. When the bot
             * offered none at the start, or when the student names
             * a role, the ones that role needs are offered.
             */
            $suggested = $context['fill_session']['skills']['suggested'] ?? null;

            if ($next === null && $choices === null && $suggested !== null
                && ($startsNow || $this->namesRole($question, $suggested['role']))) {
                $offer = collect($suggested['competencies'])
                    ->reject(fn (string $name) => $this->skillLevels->isTicked($formState, $name))
                    ->values();

                if ($offer->isNotEmpty()) {
                    /* The bot's own words stay; the buttons are added, and said so if it did not. */
                    if (! Str::contains(Str::lower((string) $validated['message']), Str::lower($suggested['role']))) {
                        $validated['message'] = rtrim((string) $validated['message'])."\n\n"
                            .'Here are the competencies **'.$suggested['role'].'** needs. Pick the ones you have done.';
                    }

                    $choices = ['options' => $offer->push('Something else')->all(), 'multiple' => true];
                    $validated['links'] = [];
                    $validated['asking_field'] = 'competencies';
                }
            }
        }

        /*
         * Filling happens on Edit Profile. Asked from anywhere else,
         * the student is taken there and the form is filled on
         * arrival.
         */
        /* A new milestone is filled in on the Milestones page, the rest on Edit Profile. */
        $onlyMilestone = $fill !== null && array_keys($fill) === ['milestone'];

        if ($onlyMilestone && $currentPage !== 'milestones') {
            $validated['navigate'] = ['page' => 'milestones', 'place' => 'add'];
        } elseif (! $onlyMilestone && ($fill !== null || $memory['section'] !== null) && $currentPage !== 'profile_edit') {
            /* A section being filled in is filled in on Edit Profile: the student is taken there. */
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

        /* Turned off on their account: the way there stays a button, and nothing moves. */
        if (! $autoNavigate) {
            $navigate = null;
        }

        $tokens = $this->tokensUsed($response, $context, $history, $question, $validated['message']);

        $memory['field'] = $memory['section'] !== null
            ? Str::limit(trim((string) ($validated['asking_field'] ?? '')), 60, '') ?: null
            : null;

        $this->recordExchange($conversation, $question, $validated['message'], $links, $tokens, $memory);

        /* Saved by now, so the item being worked on can be remembered with it. */
        $this->rememberItems($conversation, $fill);
        $this->collect($conversation, $memory, $fill);

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
                'size' => $this->memorySize($conversation),
            ],
            'tokens' => $tokens,
            'conversation' => $conversation,
        ];
    }

    /**
     * A backed-up fill without the field the reply is asking about.
     * A list item left with no name to land on is dropped whole, so
     * it cannot turn up on the form as a new, nameless item.
     *
     * @param  array<string, mixed>|null  $fill
     * @return array<string, mixed>|null
     */
    private function withoutAskedField(?array $fill, string $askingField): ?array
    {
        if ($fill === null) {
            return null;
        }

        $field = Str::lower(trim(Str::afterLast(trim($askingField), '.')));

        if ($field !== '' && $field !== 'finish') {
            foreach ($fill as $part => $value) {
                if (! is_array($value)) {
                    continue;
                }

                if (array_is_list($value)) {
                    $fill[$part] = array_values(array_filter(array_map(
                        function ($item) use ($field, $part) {
                            if (! is_array($item)) {
                                return $item;
                            }

                            unset($item[$field]);

                            $names = self::ITEM_KEYS[$part] ?? null;

                            if ($names !== null && trim((string) ($item[$names[0]] ?? '')) === '' && trim((string) ($item[$names[1]] ?? '')) === '') {
                                return null;
                            }

                            return $item;
                        },
                        $value
                    ), fn ($item) => $item !== null && $item !== []));
                } else {
                    unset($fill[$part][$field]);
                }

                if ($fill[$part] === []) {
                    unset($fill[$part]);
                }
            }
        }

        return $fill === [] ? null : $fill;
    }

    /**
     * The bot writes its reply before anything reaches the form, so
     * it cannot know a change worked. When it sent no change at all,
     * or none that matched the form, a claim to have made one is
     * replaced with the truth. When it did send one, the claim is
     * put in the present ("I'm setting ..."): the form itself then
     * confirms what really changed, or says what could not be set.
     *
     * @param  array<string, mixed>|null  $fill
     */
    private function honestAboutChanges(string $message, ?array $fill, ?string $currentPage, bool $autoNavigate = true, string $question = '', string $fillPage = 'profile_edit'): string
    {
        $fillPageName = $fillPage === 'milestones' ? 'Milestones' : 'Edit Profile';

        if ($fill !== null) {
            /*
             * Something really is going onto the form, and the form
             * itself confirms what landed, so the bot's own words
             * are left as they are. From another page it is taken
             * to Edit Profile first, and told so if it did not say.
             */
            if ($currentPage !== $fillPage && ! Str::contains(Str::lower($message), Str::lower($fillPageName))) {
                $message = rtrim($message).' '.($autoNavigate
                    ? "I'm opening ".$fillPageName.' so you can see it fill in.'
                    : 'Open '.$fillPageName.' to see it there.');
            }

            return $message;
        }

        /*
         * Nothing was sent to the form. Only when the student had
         * asked for a change, and the bot answered as if it had
         * made it, is the reply replaced: a summary of changes made
         * earlier, or a reply to anything else, is left alone.
         */
        $askedForChange = (bool) preg_match(
            '/\b(change|set|update|add|make|put|edit|fill|rename|mark|replace|correct|tick|untick|finish(ed)?|graduated?)\b/iu',
            $question
        );

        $claimed = (bool) preg_match(
            '/\b(?:I[\'’]ve|I have)\s+(?:now\s+|also\s+|just\s+)?(updated|set|changed|added|filled(?: in)?|put|ticked|unticked|selected|edited|entered|replaced|renamed|marked)\b/iu',
            $message
        );

        if ($askedForChange && $claimed) {
            return "I haven't put that on the form yet, as I wasn't sure exactly what to change. "
                .'Which item is it, and what should it be?';
        }

        return $message;
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
     * End the part of the conversation the bot remembers, without
     * asking it: finishing the section being filled in, or simply
     * clearing its memory when no section is.
     *
     * @return array{type: string, section: string, title: string}|null
     */
    public function endMemory(CpbnBotConversation $conversation): ?array
    {
        $section = $conversation->fill_section;

        $marker = $section !== null && isset(CpbnBotProfileFill::sections()[$section])
            ? $this->marker('end', $section)
            : ['type' => 'clear', 'section' => '', 'title' => ''];

        DB::transaction(function () use ($conversation, $marker): void {
            $divider = $this->recordMarker($conversation, $marker);

            $conversation->forceFill([
                'fill_section' => null,
                'fill_field' => null,
                'memory_from_message_id' => $divider->id,
            ])->save();
        });

        return $marker;
    }

    /**
     * How much the bot is carrying, so the student can see it: every
     * question sends all of it again, so a long memory makes every
     * question cost more of their allowance.
     *
     * @return array{messages: int, tokens: int}
     */
    public function memorySize(CpbnBotConversation $conversation): array
    {
        if (! $conversation->exists) {
            return ['messages' => 0, 'tokens' => 0];
        }

        $remembered = collect($this->history($conversation));

        return [
            'messages' => $remembered->count(),
            'tokens' => (int) ceil($remembered->sum(fn (array $message) => strlen($message['content'])) / 4),
        ];
    }

    /**
     * Whether a short reply just accepts what was shown and asks to
     * carry on, rather than answering or changing anything.
     */
    private function isMovingOn(string $question): bool
    {
        $text = trim($question);

        return mb_strlen($text) <= 60 && (bool) preg_match(
            "/^(i said )?(ok|okay|k|yes|yeah|yep|yup|ya|sure|fine|good|great|perfect|cool|nice|alright|done|next|continue|carry on|go on|move on|(nope|no),? (that['’]?s|it['’]?s|its) (good|fine|ok|okay)|let['’]?s (go|move on|continue|get a move on)|that['’]?s (good|fine|great|ok|okay|it)|looks good|sounds good|use (it|that|this))\\b/iu",
            $text
        );
    }

    /**
     * The saved part of the profile the section in progress needs,
     * or nothing outside a section.
     *
     * @param  array<string, mixed>  $saved
     * @return array<string, mixed>|null
     */
    private function savedSection(array $saved, ?string $section): ?array
    {
        $parts = [
            'personal' => ['personal', 'nationality_options'],
            'qualifications' => ['qualifications'],
            'skills' => ['competencies'],
            'interests' => ['interests', 'interest_options'],
            'projects' => ['projects'],
            'certifications' => ['certifications'],
            'aspirations' => ['aspirations'],
        ][$section] ?? null;

        if ($parts === null) {
            return null;
        }

        return ['fillable_sections' => $saved['fillable_sections'] ?? []]
            + array_intersect_key($saved, array_flip($parts));
    }

    /**
     * The list fields an item is known by, and the field that
     * names an existing one when it is changed.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const ITEM_KEYS = [
        'projects' => ['title', 'match_title'],
        'certifications' => ['certification_name', 'match_name'],
        'qualifications' => ['programme_name', 'match_programme'],
    ];

    /**
     * Makes a change to the item being talked about edit that item
     * rather than add another.
     *
     * The bot does not always say which existing item it means.
     * When it sends a project (say) with no name at all, it is the
     * one being worked on. When the student is plainly changing
     * something ("change", "rename", "actually", "should be") and
     * the name sent is not on the form, it is the one being worked
     * on, renamed.
     *
     * @param  array<string, mixed>  $rawFill
     * @param  array<string, mixed>|null  $formNow
     * @return array<string, mixed>
     */
    private function sameItem(array $rawFill, ?array $formNow, string $question, CpbnBotConversation $conversation): array
    {
        $current = $conversation->exists ? (array) Cache::get($this->itemsKey($conversation), []) : [];
        $changing = (bool) preg_match('/\b(change|changed|edit|rename|update|fix|correct|actually|instead|wrong|should be|meant|not\s+\S+\s+but)\b/iu', $question);

        foreach (self::ITEM_KEYS as $list => [$key, $match]) {
            if (! is_array($rawFill[$list] ?? null)) {
                continue;
            }

            $onForm = collect($formNow[$list] ?? [])
                ->map(fn ($item) => is_array($item) ? Str::lower(trim((string) ($item[$key] ?? ''))) : '')
                ->filter()
                ->all();

            $rawFill[$list] = collect($rawFill[$list])
                ->map(function ($row) use ($current, $list, $key, $match, $onForm, $changing) {
                    if (! is_array($row) || filled($row[$match] ?? null) || ! isset($current[$list])) {
                        return $row;
                    }

                    $name = trim((string) ($row[$key] ?? ''));
                    $known = $name !== '' && in_array(Str::lower($name), $onForm, true);

                    if ($name === '' || ($changing && ! $known && Str::lower($name) !== Str::lower($current[$list]))) {
                        $row[$match] = $current[$list];
                    }

                    return $row;
                })
                ->all();
        }

        return $rawFill;
    }

    /**
     * The item each list is working on now: the last one filled.
     *
     * @param  array<string, mixed>|null  $fill
     */
    private function rememberItems(CpbnBotConversation $conversation, ?array $fill): void
    {
        if ($fill === null || ! $conversation->exists) {
            return;
        }

        $current = (array) Cache::get($this->itemsKey($conversation), []);

        foreach (self::ITEM_KEYS as $list => [$key, $match]) {
            $last = collect($fill[$list] ?? [])->last();

            if (is_array($last) && filled($last[$key] ?? $last[$match] ?? null)) {
                $current[$list] = (string) ($last[$key] ?? $last[$match]);
            }
        }

        Cache::put($this->itemsKey($conversation), $current, now()->addHours(6));
    }

    /**
     * What has been sent to the form this section, merged.
     *
     * @return array<string, mixed>
     */
    private function collected(CpbnBotConversation $conversation, string $section): array
    {
        if (! $conversation->exists) {
            return [];
        }

        $saved = (array) Cache::get('cpbn-bot-collected:'.$conversation->id, []);

        return ($saved['section'] ?? null) === $section ? (array) ($saved['fill'] ?? []) : [];
    }

    /**
     * Adds this reply's fill to what has been collected this
     * section; a new section starts afresh.
     *
     * @param  array<string, mixed>  $memory
     * @param  array<string, mixed>|null  $fill
     */
    private function collect(CpbnBotConversation $conversation, array $memory, ?array $fill): void
    {
        $section = $memory['section'] ?? null;
        $key = 'cpbn-bot-collected:'.$conversation->id;

        if ($section === null) {
            Cache::forget($key);

            return;
        }

        $saved = (array) Cache::get($key, []);
        $collected = ($saved['section'] ?? null) === $section ? (array) ($saved['fill'] ?? []) : [];

        foreach ((array) $fill as $part => $value) {
            if (! is_array($value)) {
                continue;
            }

            if (isset(self::ITEM_KEYS[$part])) {
                [$name, $match] = self::ITEM_KEYS[$part];
                $items = collect($collected[$part] ?? [])->keyBy(fn ($item) => Str::lower((string) ($item[$name] ?? '')));

                foreach ($value as $row) {
                    $old = Str::lower((string) ($row[$match] ?? $row[$name] ?? ''));
                    $merged = array_merge((array) $items->get($old, []), array_diff_key($row, [$match => true]));
                    $items->forget($old);
                    $items->put(Str::lower((string) ($merged[$name] ?? $old)), $merged);
                }

                $collected[$part] = $items->values()->all();
            } elseif (array_is_list($value)) {
                $collected[$part] = array_values(array_unique(array_merge((array) ($collected[$part] ?? []), array_map(
                    fn ($item) => is_array($item) ? ($item['name'] ?? json_encode($item)) : $item,
                    $value
                ))));
            } else {
                $collected[$part] = array_merge((array) ($collected[$part] ?? []), $value);
            }
        }

        Cache::put($key, ['section' => $section, 'fill' => $collected], now()->addHours(6));
    }

    private function itemsKey(CpbnBotConversation $conversation): string
    {
        return 'cpbn-bot-items:'.$conversation->id;
    }

    /**
     * Whether the student is turning the bot's page changes off
     * (false) or back on (true). Null when they are not.
     */
    private function navigationPreference(string $question): ?bool
    {
        $text = Str::lower(trim($question));

        if (mb_strlen($text) > 160) {
            return null;
        }

        $moving = '(tak(e|ing)|send(ing)?|bring(ing)?|mov(e|ing)|navigat\w*|redirect\w*|switch(ing)?|chang(e|ing)|go(ing)?|jump(ing)?)';

        if (preg_match("/\\b(don['’]?t|do not|never|stop|no more)\\b.{0,25}\\b{$moving}\\b.{0,40}\\b(pages?|automatic\\w*|by (it|your)self|on (its|your) own)\\b/u", $text)) {
            return false;
        }

        if (preg_match("/\\b(you can|please|go ahead and|start|ok to|okay to|allowed to)\\b.{0,20}\\b{$moving}\\b.{0,40}\\b(pages?|automatic\\w*|again)\\b/u", $text)
            && ! preg_match("/\\b(don['’]?t|do not|never|stop)\\b/u", $text)) {
            return true;
        }

        return null;
    }

    /** Whether the message names this role, plural or not. */
    private function namesRole(string $question, string $role): bool
    {
        $words = fn (string $text) => collect(preg_split('/[^a-z0-9]+/', Str::lower($text)) ?: [])
            ->filter()
            ->map(fn (string $word) => Str::singular($word))
            ->implode(' ');

        return Str::contains(' '.$words($question).' ', ' '.$words($role).' ');
    }

    /**
     * A short message that plainly ends the part being filled in
     * ("I'm done", "ok you can stop now", "that's all"), whatever
     * the bot made of it.
     */
    private function saysStop(string $question): bool
    {
        $text = trim($question);

        return mb_strlen($text) <= 40 && (bool) preg_match(
            "/^(ok(ay)?,? |alright,? |no,? |nope,? |thanks,? |yeah,? )?(i['’]?m done|i am done|all done|we['’]?re done|done|that['’]?s (it|all|everything|enough)|nothing else|no more|((you|we) can |let['’]?s )?stop( now| here| there| the session| this| for now)?|(end|finish|close) (the |this )?session|finished|i['’]?m finished|never ?mind|no thanks?( you)?|i['’]?m good|that['’]?s enough( for now)?|i['’]?ll do (the rest|it) myself|leave it( there)?)[.,! ]*(thanks|thank you)?[.! ]*$/iu",
            $text
        );
    }

    /**
     * Whether the student is saying the section is finished.
     *
     * A plain answer counts only as the reply to the bot's own closing
     * question, "anything else to add or change?", which it marks by
     * asking about the field "finish": there "no" or "nothing" means
     * finished, and so does a yes to being happy with it. Anywhere
     * else a yes is accepting a suggestion and a no is a correction.
     */
    private function saysFinished(string $question, bool $wasAsked): bool
    {
        if ($wasAsked && preg_match("/^\\s*(yes|yeah|yep|yup|ya|ok|okay|sure|correct|perfect|great|good|done|all good|no|nope|nah|nothing|none|that['’]?s all)\\b/iu", $question)) {
            return true;
        }

        return (bool) preg_match(
            "/\\b(that['’]?s (it|everything|all)|i['’]?m done|all done|finish(ed)?|we['’]?re done|looks good|nothing else)\\b/iu",
            $question
        );
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
                'fill_field' => $memory['field'] ?? null,
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
