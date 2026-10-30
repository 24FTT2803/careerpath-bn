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
            'Describe only pages, buttons, sections and rules that appear in the context. If the context does not say how to do something, say you are not sure and suggest the page most likely to have it. Never make up a feature, a limit, a price or a date.',
            '',
            'THE STUDENT',
            '',
            'student says where they have got to: profile completion and the sections still to fill, Student ID, plan, recommendations and milestones. Use it when they ask what to do next or why something is not working, for example a profile section still to fill, or no generations left. Address them by first_name occasionally, not in every reply.',
            '',
            'LINKS',
            '',
            'current_page is the page the student is asking from. They can already see it: describe where things are on it ("just under the Qualifications heading"), not how to get there. A link to a particular place inside it, such as one role\'s competencies in the BIICF Explorer, is still useful.',
            '',
            'links are buttons shown beneath the reply. Each has a page key, one of: '.implode(', ', CpbnBotKnowledge::pageKeys()).'. Add a link only when it takes the student somewhere they are not: another page, or a particular place in the BIICF Explorer. Most answers need none; never add a plain link to current_page. At most two. Never write web addresses in the message.',
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
                    ],
                    'required' => ['schema_version', 'status', 'message', 'links', 'navigate'],
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
            ],
            'required' => ['page', 'explorer_section', 'sub_sector', 'job_role', 'tier', 'competency', 'proficiency_level'],
            'additionalProperties' => false,
        ];
    }
}
