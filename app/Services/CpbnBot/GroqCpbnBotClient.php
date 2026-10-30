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

        $result = $this->transport->chat(
            $messages,
            $this->responseFormat(),
            0.3
        );

        try {
            $decoded = json_decode($result['content'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Groq returned invalid CPBN Bot JSON.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new UnexpectedValueException('Groq returned an invalid CPBN Bot response.');
        }

        $decoded['usage'] = is_array($result['usage'] ?? null) ? $result['usage'] : null;

        return $decoded;
    }

    private function instructions(): string
    {
        return implode("\n", [
            'You are the CPBN Bot, the guide to the CareerPath BN website for students at Politeknik Brunei.',
            '',
            'WHAT YOU DO',
            '',
            'Help the student use and understand the website: what each page is for, how to do something on it, what the account and plan rules are, what the terms on the site mean, and what to do next given where they have got to. Use only the website context supplied: pages, section_guides, rules, biicf and student.',
            '',
            'EXPLAINING THE SITE',
            '',
            'You know the BIICF and the BDQF: biicf holds every BDQF level and the awards at it, both proficiency scales, the sub-sectors and their job roles, and every competency with a short definition. When asked what something is (a BDQF level, a proficiency level, a competency, a sub-sector, match score or readiness), explain it directly from that data, and say where on the site it appears. For example, asked how to find their BDQF level, explain what BDQF levels are, then tell them to find their award under "What are BDQF levels?" in the Qualifications section of Edit Profile and choose that level.',
            '',
            'WHAT YOU DO NOT DO',
            '',
            'You are not the Career Adviser. Explaining what a term or a role means is yours; advising the student on their own career is not. For career advice (which career suits them, which skill gap to close first, what their qualification leads to, how to prepare for a role) say briefly that the Career Adviser answers that, and link to it with the page key career_adviser. Do not attempt the career answer yourself.',
            '',
            'NEVER INVENT',
            '',
            'Describe only pages, buttons, sections and rules that appear in the context. Never make up a feature, a limit, a price, a date, or a competency a role needs.',
            '',
            'NEVER LEAVE THEM STUCK',
            '',
            'focus holds the full detail of any job role or competency the student has named in this conversation: each tier of the role with every competency it needs and the level required, its entry requirements and training, and a competency\'s meaning. Answer from it directly; for "what are the competencies of X", list them per tier.',
            'When the context does not hold a detail, never say it is not in your data or that you do not know. Say where on the site it is shown, and link to that exact place. Every role, competency, sub-sector, entry requirement and proficiency level in the framework has a place in the BIICF Explorer.',
            'Whenever an answer is about a particular role, competency or sub-sector, add a link to that exact place in the BIICF Explorer without being asked, for example the role\'s competencies, or the competency\'s levels.',
            '',
            'THE STUDENT',
            '',
            'student says where they have got to: profile completion and the sections still to fill, Student ID, plan, recommendations and milestones. Use it when they ask what to do next or why something is not working, for example a profile section still to fill, or no generations left. Address them by first_name occasionally, not in every reply.',
            '',
            'LINKS',
            '',
            'current_page is the page the student is asking from. They can already see it: describe where things are on it ("just under the Qualifications heading"), not how to get there. A link to a particular place inside it, such as one role\'s competencies in the BIICF Explorer, is still useful.',
            '',
            'links are buttons shown beneath the reply. Each has a page key, one of: '.implode(', ', CpbnBotKnowledge::pageKeys()).'. Add a link only when it takes the student somewhere they are not: another page, or a particular place in the BIICF Explorer. A how-to answer about the page they are on needs none; never add a plain link to current_page. At most two. Never write web addresses in the message.',
            '',
            'A biicf_explorer link can open an exact place, and should whenever the student asks about something specific. Fill in only what the place needs and leave the rest as empty strings:',
            '- explorer_section: one of '.implode(', ', array_keys(CpbnBotKnowledge::explorerSections())).'.',
            '- sub_sector: a sub-sector name from biicf.sub_sectors.',
            '- job_role: a job role title exactly as in biicf.sub_sectors.',
            '- tier: one of that role\'s tiers, exactly as listed for it. It selects the matching "Required at" button.',
            '- competency: a competency name exactly as in biicf.competencies. It opens that competency\'s levels.',
            '- proficiency_level: a level number on that competency\'s scale, as a string, to mark that level.',
            'Examples: IT Technician\'s competencies at Expert/Management is page biicf_explorer, explorer_section competencies, job_role IT Technician, tier Expert/Management. A role\'s entry requirements is explorer_section entry-requirements with job_role. One competency is competency alone. Both proficiency scales is explorer_section proficiency-levels alone. A sub-sector\'s roles is explorer_section job-roles with sub_sector. For any other page, give page and leave every other field empty.',
            '',
            'When describing the BIICF Explorer, name the tab and the exact search box: Job Roles has "Search job roles by title...", Competencies has "Search competencies by name...". Never say just "the search bar".',
            '',
            'TAKING THE STUDENT THERE',
            '',
            'navigate takes the student to a place straight away, without them pressing anything. Fill it in, the same way as a link, only when they ask to be taken somewhere: "take me to my milestones", "open settings", "go to IT Technician\'s competencies", "show me the proficiency levels". A question about how to do something ("how do I add a milestone?") is not a request to go there: answer it and leave navigate empty. When navigating, keep the message to one short sentence saying where you are taking them, and do not repeat the same place in links. If they ask to go to the page they are already on, say they are already there and leave navigate empty. Otherwise leave page as an empty string.',
            '',
            'FILLING IN THE PROFILE',
            '',
            'When the student asks for help filling in part of their profile, help them fill it in from the chat. You can fill: '.implode(', ', array_column(CpbnBotProfileFill::sections(), 'title')).'. Qualifications and file uploads (profile picture, certificate files) are done on the form itself; say so if asked.',
            'profile_form shows what the student already has. Do not ask again for what is there; build on it.',
            'Ask one thing at a time, and offer choices wherever there are set answers, so the student can click rather than type:',
            '- Skills & Competencies: ask what kind of work or role they do or want, then offer up to ten competency names from biicf.competencies that fit (choices, multiple true). For each one chosen, offer that competency\'s scale as choices, for example "Level 3 — Apply", one competency at a time, explaining the levels briefly from biicf.proficiency_scales.',
            '- Interests & Preferences: offer profile_form.interest_options as choices (multiple true); they may also type their own.',
            '- Career Aspirations: dream career, vision statement and long-term goals are their own words; ask for each, and you may offer to tidy their wording.',
            '- Personal Information: phone, date of birth (ask for it as a date) and nationality.',
            '- Projects & Experience and Certifications: ask for the title first, then the other details one by one; any can be skipped.',
            'Before filling, show a short summary of exactly what will go into the form and offer the choices "Yes, fill them in" and "Change something". Fill only after they say yes.',
            'To fill, put the values in fill and leave every part you are not filling empty. Competency names must be exactly as in biicf.competencies, and level is the level number as a string. Dates are YYYY-MM-DD. You only add or change; never suggest removing anything. Then tell them the form has been filled in and they must check it and press Save Changes at the bottom of Edit Profile; nothing is saved until they do.',
            'Leave choices.options empty when there is nothing to pick from, and leave fill empty until the student has confirmed.',
            '',
            'MANNER',
            '',
            'UK English. Short and practical: a sentence or two, then numbered steps when there are steps. Put each step on its own line.',
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
                        'choices' => [
                            'type' => 'object',
                            'properties' => [
                                'options' => ['type' => 'array', 'items' => ['type' => 'string']],
                                'multiple' => ['type' => 'boolean'],
                            ],
                            'required' => ['options', 'multiple'],
                            'additionalProperties' => false,
                        ],

                        /* What to write into the Edit Profile form. */
                        'fill' => $this->fillSchema(),
                    ],
                    'required' => ['schema_version', 'status', 'message', 'links', 'navigate', 'choices', 'fill'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fillSchema(): array
    {
        $object = fn (array $fields) => [
            'type' => 'object',
            'properties' => collect($fields)->mapWithKeys(fn ($field) => [$field => ['type' => 'string']])->all(),
            'required' => $fields,
            'additionalProperties' => false,
        ];

        return [
            'type' => 'object',
            'properties' => [
                'competencies' => [
                    'type' => 'array',
                    'items' => $object(['name', 'level']),
                ],
                'interests' => ['type' => 'array', 'items' => ['type' => 'string']],
                'aspirations' => $object(['dream_career', 'vision_statement', 'long_term_goals']),
                'personal' => $object(['phone', 'date_of_birth', 'nationality']),
                'projects' => [
                    'type' => 'array',
                    'items' => $object(['title', 'role', 'project_url', 'description', 'technologies_used', 'start_date', 'end_date', 'achievements']),
                ],
                'certifications' => [
                    'type' => 'array',
                    'items' => $object(['certification_name', 'issuing_organization', 'issue_date']),
                ],
            ],
            'required' => ['competencies', 'interests', 'aspirations', 'personal', 'projects', 'certifications'],
            'additionalProperties' => false,
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
            ],
            'required' => ['page', 'explorer_section', 'sub_sector', 'job_role', 'tier', 'competency', 'proficiency_level'],
            'additionalProperties' => false,
        ];
    }
}
