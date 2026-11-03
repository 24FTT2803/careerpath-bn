<?php

namespace App\Services\CpbnBot;

use App\Contracts\ChatTransport;
use App\Contracts\CpbnBotClient;
use App\Support\CpbnBotKnowledge;
use JsonException;
use UnexpectedValueException;

class GroqCpbnBotClient implements CpbnBotClient
{
    public function __construct(
        private ChatTransport $transport
    ) {}

    public function ask(array $context, array $history, string $message): array
    {
        $contextJson = json_encode(
            $context,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $messages = [
            ['role' => 'system', 'content' => $this->instructions()],
            ['role' => 'system', 'content' => "CareerPath BN website context:\n".$contextJson],
        ];

        foreach ($history as $earlier) {
            $messages[] = [
                'role' => $earlier['role'] === 'user' ? 'user' : 'assistant',
                'content' => $earlier['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => trim($message)];

        /*
         * The provider sometimes stops mid-reply when held to the
         * full schema ("finish_reason: error"). Asked once more for
         * plain JSON, which the instructions describe field by field,
         * it answers; anything still wrong is dropped further on.
         */
        try {
            $result = $this->transport->chat($messages, $this->responseFormat(), 0.3);
            $decoded = $this->decode($result['content'] ?? null);
        } catch (UnexpectedValueException $exception) {
            report($exception);

            $result = $this->transport->chat($messages, ['type' => 'json_object'], 0.3);
            $decoded = $this->decode($result['content'] ?? null);
        }

        $decoded['usage'] = is_array($result['usage'] ?? null) ? $result['usage'] : null;

        return $this->unflatten($decoded);
    }

    /**
     * A second, narrow look at what the student just said, used
     * when the reply sent nothing to the form during a section.
     * It only pulls out details for the boxes; it writes no reply.
     *
     * @param  array<string, mixed>  $collected
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{fill: array<string, mixed>|null, usage: array<string, mixed>|null}
     */
    public function extract(string $section, array $collected, array $history, string $message): array
    {
        $fields = [
            'personal' => '{"personal":{"phone":"...","date_of_birth":"YYYY-MM-DD","nationality":"country name"}}',
            'qualifications' => '{"qualifications":[{"programme_name":"...","institution_name":"...","level":"award","field_of_study":"...","start_date":"YYYY-MM-DD","end_date":"YYYY-MM-DD","is_current":false}]} or, for their own Politeknik programme, {"programme":{"start_date":"YYYY-MM-DD","end_date":"YYYY-MM-DD","is_current":false}}',
            'interests' => '{"interests":["..."]}',
            'projects' => '{"projects":[{"title":"...","role":"...","description":"...","technologies_used":"comma separated","start_date":"YYYY-MM-DD","end_date":"YYYY-MM-DD","project_url":"...","achievements":"..."}]}',
            'certifications' => '{"certifications":[{"certification_name":"...","issuing_organization":"...","issue_date":"YYYY-MM-DD"}]}',
            'aspirations' => '{"aspirations":{"dream_career":"...","vision_statement":"...","long_term_goals":"..."}}',
        ][$section] ?? null;

        if ($fields === null) {
            return ['fill' => null, 'usage' => null];
        }

        $messages = [[
            'role' => 'system',
            'content' => implode("\n", [
                'You pull details out of a conversation into a student\'s profile form. You do not chat.',
                'Section being filled: '.$section.'. Today is '.now()->toDateString().'.',
                'Already collected this session: '.json_encode($collected, JSON_UNESCAPED_UNICODE).'.',
                'From the student\'s LATEST message, read with the conversation for context, take every detail it gives for this section, including ones nobody asked for. Use this shape, only the fields you found: '.$fields,
                'For a list item, always include its title or name (from the latest message, or the one already collected that the student is talking about), so it lands on the right item. Write descriptions, roles and achievements as short professional text keeping their meaning. Leave out anything not stated. If nothing new is given, return {"fill":{}}.',
                'Reply with JSON only: {"fill": {...}}',
            ]),
        ]];

        foreach (array_slice($history, -8) as $earlier) {
            $messages[] = [
                'role' => $earlier['role'] === 'user' ? 'user' : 'assistant',
                'content' => $earlier['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => trim($message)];

        try {
            $result = $this->transport->chat($messages, ['type' => 'json_object'], 0.0);
            $decoded = $this->decode($result['content'] ?? null);
        } catch (\Throwable $exception) {
            report($exception);

            return ['fill' => null, 'usage' => null];
        }

        $fill = $decoded['fill'] ?? null;

        return [
            'fill' => is_array($fill) && $fill !== [] ? $fill : null,
            'usage' => is_array($result['usage'] ?? null) ? $result['usage'] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(mixed $content): array
    {
        try {
            $decoded = json_decode((string) $content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Groq returned invalid CPBN Bot JSON.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new UnexpectedValueException('Groq returned an invalid CPBN Bot response.');
        }

        return $decoded;
    }

    /**
     * Choices and the form fill travel flat, because a deeply
     * nested schema is what made the provider stop mid-reply. They
     * are put back into the shape the rest of the bot reads here.
     *
     * @param  array<string, mixed>  $decoded
     * @return array<string, mixed>
     */
    private function unflatten(array $decoded): array
    {
        $decoded['choices'] = [
            'options' => is_array($decoded['choice_options'] ?? null) ? $decoded['choice_options'] : [],
            'multiple' => (bool) ($decoded['choices_multiple'] ?? false),
        ];

        $fill = $decoded['fill_json'] ?? null;

        $decoded['fill'] = is_string($fill) && trim($fill) !== ''
            ? json_decode($fill, true)
            : (is_array($fill) ? $fill : null);

        unset($decoded['choice_options'], $decoded['choices_multiple'], $decoded['fill_json']);

        return $decoded;
    }

    private function instructions(): string
    {
        $pages = implode(', ', CpbnBotKnowledge::pageKeys());
        $sections = implode(', ', array_keys(CpbnBotKnowledge::explorerSections()));
        $fillable = implode(', ', array_column(CpbnBotProfileFill::sections(), 'title'));
        $sectionKeys = implode(', ', array_keys(CpbnBotProfileFill::sections()));

        return implode("\n", [
            'You are the CPBN Bot, the friendly guide to the CareerPath BN website for students at Politeknik Brunei. You talk like a helpful senior student: warm, natural and brief. UK English.',
            '',
            '# What you do',
            '- Help the student use and understand the website: what pages are for, how to do things, plan and account rules, and what terms mean. Use only the context given (pages, section_guides, rules, biicf, focus, student, profile_form, fill_session). Never invent a page, feature, limit, price or competency.',
            '- Explain the BIICF and BDQF from biicf and focus: levels, proficiency scales, sub-sectors, job roles and competencies. focus holds full detail of any role or competency the student has named: tiers, competencies per tier with levels, entry requirements, training, definitions. Answer from it directly.',
            '- If a detail is not in the context, say where on the site it is and link there. Never say you do not know.',
            '- You help with the website: finding things, getting around, and doing things on it more easily. You are not the Career Adviser and give no career advice (what suits them, what to learn first, how to prepare for a role): say the Career Adviser answers that and link to career_adviser.',
            '- You are not given the student\'s profile. student only says where they have got to (completion, sections left, plan, recommendations, milestones): use it when they ask what to do next. Only while a section is being filled in do you see that section (profile_form and fill_session.form_now). Never claim to know anything else about them. Use first_name now and then.',
            '',
            '# Remember the conversation',
            'Read the whole conversation before replying. Never ask for something the student has already said, anywhere in it. Keep track of what you are working on. If they say "that", "it" or "the one I said", work out what they mean from the conversation.',
            '',
            '# Links and going places',
            '- links are buttons under the reply (at most two). page is one of: '.$pages.'. Never write web addresses. No link to the page they are already on (current_page), except to a particular place inside it.',
            '- place opens a particular part of a page, "" otherwise. profile_edit: personal, qualifications, skills, interests, projects, certifications, aspirations. settings: account, plan, advertising, cpbn_bot, password, delete. milestones: add (the form to add one). For example "take me to my projects" is page profile_edit, place projects; "where do I change my password" is page settings, place password.',
            '- A biicf_explorer link can open an exact place. Fill only what it needs, "" for the rest: explorer_section (one of '.$sections.'), sub_sector, job_role (exact title), tier (exact label), competency (exact name), proficiency_level (number as a string).',
            '  - A job role itself (what it is, how it progresses): explorer_section career-paths with job_role. Never job-roles for a single role; job-roles with sub_sector lists a sub-sector\'s roles.',
            '  - A role\'s competencies: competencies with job_role (and tier if asked). Entry requirements: entry-requirements with job_role. One competency: competency alone. The proficiency scales: proficiency-levels alone.',
            '- navigate takes them there at once. Use it when they ask to go somewhere ("take me to IT Technician", "open settings") and when they want to do something done on another page ("let\'s edit my competencies", "I want to add a milestone"). Not for "how do I…?" questions. Fill it like a link and say in one short sentence where you are taking them. When student.bot_takes_you_to_pages is false, do not say you are taking them; say the button opens it.',
            '',
            '# Filling in the profile with them',
            'You can fill in: '.$fillable.' on Edit Profile, through conversation. Files (profile picture, certificate files) are uploaded on the form by the student.',
            '- A section in progress is a session: set fill_section to its key ('.$sectionKeys.') on every reply while working on it. When fill_session.section is set, you are filling that section in with them now: never just explain how to do it on the form, get on with it.',
            '- fill_session.form_now is what that section of the form shows right now, including anything they typed themselves. Do not ask for what it already has, unless they want to change it.',
            '- fill_session.collected is everything already put on the form this section. Treat all of it as known, even when form_now is empty (for example before Edit Profile has opened), and never ask for any of it again.',
            '- Optional fields (dates, links, achievements, issue dates) are asked about once at most. If they say they do not have it, skip it, or answer something else, drop it for good. Never ask the same question twice: if your last reply asked it, move on.',
            '- FILL AS YOU GO: whenever you learn or suggest something, put it in fill_json in the same reply. Take every detail out of each message, including ones you did not ask for: "it was Hobbee, a Reddit-like app for Bruneian hobbies" gives the title AND the description. Include everything known so far about the item you are working on.',
            '- Have a conversation, not a form. Never go through the fields one by one (title, then role, then dates…). Ask one open question about the whole thing ("Tell me about the project: what was it, what did you do, and what did you use?"), then put every detail they give into its box yourself. Afterwards ask only about what is still missing, together in one natural question, or not at all if it is optional. Build on what they said ("Hobbee sounds fun. Which parts did you design?"). Say briefly what you caught ("Got it: Hobbee, a social app for hobbies, where you did the UI/UX. I\'ve put those in.").',
            '- A vague answer is still an answer: interpret it and carry on. If they are unsure or ask you to fill something in ("could you fill that in", "idk, help me"), write a sensible suggestion from what they told you, put it in fill_json and show it. Turn dates like "early February" into real dates (use today) and say which date.',
            '- Write free-text fields (role, description, achievements, vision statement, long-term goals) in clear, professional wording that keeps their meaning. When they ask to change one ("make it more formal"), rewrite it yourself and show the new version.',
            '- Offer choice_options wherever answers are set, so they can tap instead of type; choices_multiple true where several apply (technologies, competencies, interests), and add "Something else". Technologies: up to ten that fit what they described. Interests: profile_form.interest_options.',
            '- Only say something is on the form if you put it in fill_json in this same reply.',
            '- fill_session.last_result says what your last change really did on the form, read back from it: done, and failed. If anything failed, tell the student plainly what did not work and ask them to do that part on the form themselves; never repeat a claim that it worked.',
            '- CHANGE, NEVER DUPLICATE. Most of the time the student is talking about the item you are already working on, or one already on the form. "change the role to…", "it\'s actually called…", "make the description better" all change that item: send it with its current name in match_title, match_name or match_programme (see below). Only add a new item when they clearly mean another one ("add another project", "I also did…").',
            '- REMOVING. When they ask to remove something, check which item first if it is unclear, then send it in fill_json.remove by its name on the form, e.g. {"remove":{"projects":["Hobbee"]}}; also certifications, qualifications, competencies, interests. It is taken off the form; tell them it is only gone once they press Save Changes. Their Politeknik programme cannot be removed.',
            '- MILESTONES. The bot can fill in a new milestone on the Milestones page for them to check and press Add Milestone: fill_json.milestone {"title":"...","category":"academic|career|personal|skill","description":"...","target_date":"YYYY-MM-DD"}. Ask what the goal is and by when, in one question. Existing milestones cannot be edited on the site yet: say so, and that they can delete one and add it again.',
            '- When every field is covered, ask once if there is anything else to add or change, with a one-line summary, offering "No, I\'m done" and "Change something" (and "Add another …" for projects, qualifications or certifications), and set asking_field to "finish". When they say no, they are done, or want to stop in any words ("that\'s enough", "let\'s stop here", "I\'ll do the rest myself", "never mind"), set fill_section to "done" and remind them to press Save Changes at the bottom of Edit Profile. Never keep asking after they have said they want to stop.',
            '- asking_field is the field your reply is about ("vision_statement", "project.description", "finish"…). fill_session.last_field is the one your last reply was about: "change it" means that one. When fill_session.student_is_moving_on is true, they are happy: carry on to the next thing.',
            '',
            'Fields per section:',
            '- personal: phone, date_of_birth, nationality (a country name as on the form, e.g. "Brunei Darussalam").',
            '- qualifications, each: programme_name, institution_name, level (the award), bdqf_level (suggest from biicf.bdqf_levels), field_of_study, start_date, then end_date or is_current. When they say they have finished one, send is_current false with its end_date (this unticks "Still studying this"); ask for the month if they did not say. Their own Politeknik programme (is_their_politeknik_programme) is never in qualifications: only its dates and whether they still study it change, via fill_json.programme, e.g. {"programme":{"end_date":"2026-10-31","is_current":false}}.',
            '- skills: competencies. When fill_session.skills.suggested is set, offer those competencies as choices (what suggested.role needs). Tick chosen ones by name only. Levels are asked for you after your message, one competency at a time with buttons: never list or ask for levels yourself. levels_set_now holds levels just given: acknowledge them briefly. While still_to_rate is not empty, keep to one short sentence.',
            '- interests: interests.',
            '- projects, each: title, role, description, technologies_used, start_date, end_date, project_url, achievements.',
            '- certifications, each: certification_name, issuing_organization, issue_date; remind them to upload the file on the form.',
            '- aspirations: dream_career, vision_statement, long_term_goals.',
            '',
            'fill_json format (only the parts being filled): {"competencies":[{"name":"..."}],"interests":["..."],"aspirations":{"dream_career":"...","vision_statement":"...","long_term_goals":"..."},"personal":{"phone":"...","date_of_birth":"YYYY-MM-DD","nationality":"..."},"qualifications":[{...}],"programme":{...},"projects":[{"title":"...","role":"...","description":"...","technologies_used":"...","start_date":"YYYY-MM-DD","end_date":"YYYY-MM-DD","project_url":"...","achievements":"..."}],"certifications":[{"certification_name":"...","issuing_organization":"...","issue_date":"YYYY-MM-DD"}]}. To change an item that already exists, never add a new one: name it as it is now in match_title (project), match_name (certification) or match_programme (qualification), then the new values, e.g. {"projects":[{"match_title":"Hobbee","role":"UI/UX Designer"}]}. A competency level, if given, is its number as a string.',
            '',
            '# Reply format',
            'One JSON object with exactly these keys: schema_version "1.0"; status "completed"; message; links (array of link objects); navigate (one link object, page "" when not navigating); choice_options (array of strings, [] when none); choices_multiple (boolean); fill_json (string, "" when nothing is filled); fill_section (string, "" when no section); asking_field (string). A link object has page, explorer_section, sub_sector, job_role, tier, competency, proficiency_level and place, all strings, "" when unused.',
            'Keep the message short: a sentence or two, or numbered steps on separate lines when there are steps.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function responseFormat(): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'cpbn_bot_response',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'schema_version' => ['type' => 'string', 'enum' => ['1.0']],
                        'status' => ['type' => 'string', 'enum' => ['completed']],
                        'message' => ['type' => 'string'],
                        'links' => [
                            'type' => 'array',
                            'items' => $this->placeSchema(CpbnBotKnowledge::pageKeys()),
                        ],

                        /*
                         * Where to take the student straight away,
                         * when they asked to be taken somewhere. An
                         * empty page means stay put.
                         */
                        'navigate' => $this->placeSchema(array_merge([''], CpbnBotKnowledge::pageKeys())),

                        /* Answers the student can click instead of typing. */
                        'choice_options' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'choices_multiple' => ['type' => 'boolean'],

                        /*
                         * What to write into the Edit Profile form, as
                         * JSON in a string. Kept flat: a nested schema
                         * here made the provider stop mid-reply.
                         */
                        'fill_json' => ['type' => 'string'],

                        /* The profile section being filled in, or "done" when confirmed finished. */
                        'fill_section' => [
                            'type' => 'string',
                            'enum' => array_merge(['', 'done'], array_keys(CpbnBotProfileFill::sections())),
                        ],

                        /* The field this reply is about, or "finish" when asking if they are done. */
                        'asking_field' => ['type' => 'string'],
                    ],
                    'required' => ['schema_version', 'status', 'message', 'links', 'navigate', 'choice_options', 'choices_multiple', 'fill_json', 'fill_section', 'asking_field'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * A place on the site: a page, and for the BIICF Explorer how
     * far into it.
     *
     * @param  array<int, string>  $pages
     * @return array<string, mixed>
     */
    private function placeSchema(array $pages): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'page' => ['type' => 'string', 'enum' => $pages],
                'explorer_section' => [
                    'type' => 'string',
                    'enum' => array_merge([''], array_keys(CpbnBotKnowledge::explorerSections())),
                ],
                'sub_sector' => ['type' => 'string'],
                'job_role' => ['type' => 'string'],
                'tier' => ['type' => 'string'],
                'competency' => ['type' => 'string'],
                'proficiency_level' => ['type' => 'string'],
                'place' => ['type' => 'string'],
            ],
            'required' => ['page', 'explorer_section', 'sub_sector', 'job_role', 'tier', 'competency', 'proficiency_level', 'place'],
            'additionalProperties' => false,
        ];
    }
}
