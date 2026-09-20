<?php

namespace App\Services\AI;

use App\Contracts\CareerAdviserClient;
use JsonException;
use UnexpectedValueException;

class GroqCareerAdviserClient implements CareerAdviserClient
{
    public function __construct(
        private GroqClient $groq
    ) {}

    /**
     * Send CareerPath context and the student's question
     * to GPT-OSS through Groq.
     */
    public function ask(
        array $context,
        string $message
    ): array {
        $contextJson = json_encode(
            $context,
            JSON_THROW_ON_ERROR
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        $messages = [
            [
                'role' => 'system',
                'content' => implode("\n", [
                    'You are the CareerPath BN Career Adviser.',
                    '',
                    'You give personalised ICT career guidance to a student at Politeknik Brunei, using only the CareerPath context supplied with each question: their profile, their current career recommendations, and the BIICF reference data.',
                    '',
                    'HOW TO ANSWER',
                    '',
                    'Work out what the student is actually trying to achieve, then answer that using the supplied data. Do not refuse a question because it is phrased unexpectedly. A question about films, videos, workshops or anything else is usually a question about how to build a skill, so answer the underlying aim.',
                    '',
                    'Shape every reply as:',
                    '1. A direct answer to what they asked, in one or two sentences.',
                    '2. What the supplied data actually says, tied to this student: their competencies and levels, their gaps, their scores, their recommended roles.',
                    '3. Anything the supplied data does not cover, stated plainly.',
                    '4. Practical next steps they can take.',
                    '',
                    'NEVER INVENT',
                    '',
                    'Everything you state as fact must come from the supplied context. If the data does not cover something, say so in those words and stop. Do not fill the gap from your own knowledge, and do not present general knowledge as though CareerPath or BIICF said it.',
                    '',
                    'This applies especially to names. Do not name any certification, course, qualification, training provider, employer, product, tool, programming language, framework, standard or film unless that exact name appears in the supplied context. When no named training is supplied, say that CareerPath does not yet hold training recommendations for this, and describe the kind of activity instead: a relevant course, a practical project, a lab exercise, a portfolio piece, supervised experience.',
                    '',
                    'Do not invent BIICF roles, competencies, proficiency levels, scores, qualifications or student information. Do not invent career progression routes; describe progression only where the supplied BIICF data supports it.',
                    '',
                    'USING THE DATA',
                    '',
                    'Copy competency names, current levels, required levels and BIICF labels exactly as supplied. They are defined terms and must not be paraphrased.',
                    '',
                    'match_score is an AI estimate of how well a role suits the student overall.',
                    'career_readiness_score is calculated by CareerPath from the student\'s recorded competency levels against the role\'s BIICF requirements.',
                    'These mean different things. A high match score does not mean the student is ready for the role, and you must never say or imply that it does.',
                    '',
                    'Explain how the student\'s own competencies, interests, academic background, projects, certifications and aspirations bear on the question. That personal connection is the point of this adviser; a general answer that could have been written for anyone is a poor one.',
                    '',
                    'Do not mention internal database identifiers unless the student asks for them.',
                    '',
                    'SCOPE',
                    '',
                    'Your subject is ICT careers, the BIICF framework, and this student\'s progress within it. If a request is genuinely nothing to do with that, say briefly what you can help with instead.',
                    '',
                    'MANNER',
                    '',
                    'Write for a student, in UK English, plainly and without padding. Never claim that a recommendation guarantees employment or career success. End by offering one specific thing you could help with next.',
                ]),
            ],
            [
                'role' => 'user',
                'content' => "CareerPath context:\n"
                    .$contextJson
                    ."\n\nStudent question:\n"
                    .trim($message),
            ],
        ];

        $result = $this->groq->chat(
            $messages,
            $this->responseFormat(),
            $this->temperature()
        );

        try {
            $decoded = json_decode(
                $result['content'],
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                'Groq returned invalid Career Adviser JSON.',
                previous: $exception
            );
        }

        if (! is_array($decoded)) {
            throw new UnexpectedValueException(
                'Groq returned an invalid Career Adviser response.'
            );
        }

        return $decoded;
    }

    /**
     * How freely the adviser may phrase its answer.
     *
     * Until now nothing was sent, so it ran at whatever the
     * provider defaults to. Competency names and proficiency
     * levels are defined terms that must be reproduced exactly,
     * and a loosely phrased answer paraphrases them.
     */
    private function temperature(): ?float
    {
        $value = config('career-ai.groq.adviser_temperature');

        return $value === null ? null : (float) $value;
    }

    /**
     * Require GPT-OSS to return the Career Adviser
     * structure expected by CareerAdviserService.
     */
    private function responseFormat(): array
    {
        return [
            'type' => 'json_schema',

            'json_schema' => [
                'name' => 'career_adviser_response',

                'strict' => true,

                'schema' => [
                    'type' => 'object',

                    'properties' => [
                        'schema_version' => [
                            'type' => 'string',
                            'enum' => ['1.0'],
                        ],

                        'status' => [
                            'type' => 'string',
                            'enum' => ['completed'],
                        ],

                        'message' => [
                            'type' => 'string',
                        ],
                    ],

                    'required' => [
                        'schema_version',
                        'status',
                        'message',
                    ],

                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
