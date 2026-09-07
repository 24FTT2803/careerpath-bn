<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

class GroqCareerAiClient implements CareerAiClient
{
    public function __construct(
        private GroqClient $groq
    ) {
    }

    /**
     * Generate three BIICF career recommendations
     * using GPT-OSS through Groq.
     *
     * GPT-OSS handles career judgement and explanation.
     * Laravel handles deterministic BIICF competency calculations.
     */
    public function recommend(array $payload): array
    {
        $eligibleRoles =
            $payload['eligible_roles']
            ?? [];

        if (
            ! is_array($eligibleRoles)
            || count($eligibleRoles) < 3
        ) {
            throw new RuntimeException(
                'Career recommendation context must contain at least three eligible BIICF roles.'
            );
        }

        $eligibleRoleIds = collect($eligibleRoles)
            ->pluck('id')
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $contextJson = json_encode(
            $payload,
            JSON_THROW_ON_ERROR
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        $messages = [
            [
                'role' => 'system',

                'content' => implode("\n", [
                    'You are the CareerPath BN Career Recommendation engine.',
                    '',
                    'Your task is to select and rank exactly three suitable BIICF ICT job roles for the student.',
                    '',
                    'The supplied data contains:',
                    '- the student CareerPath profile;',
                    '- eligible BIICF job roles;',
                    '- BIICF competency requirements;',
                    '- the BIICF proficiency scale.',
                    '',
                    'Rules:',
                    '- Recommend only roles contained in eligible_roles.',
                    '- Never invent a BIICF role or role ID.',
                    '- Return three different roles.',
                    '- Use ranks 1, 2 and 3 exactly once each.',
                    '- Consider the student programme, competencies, interests, academic information, projects, certifications and aspirations.',
                    '- reference.competency_catalogue maps competency IDs to [name, type].',
                    '- Each role requirement is represented as [competency_id, required_level].',
                    '- reference.proficiency_scale maps proficiency numbers to BIICF labels.',
                    '- Treat the supplied BIICF data as authoritative.',
                    '- Use the student competencies and their proficiency levels when judging current readiness.',
                    '- A missing student competency means there is no recorded evidence for that competency.',
                    '- match_score is an estimated overall career suitability score from 0 to 100.',
                    '- Base match_score primarily on career fit: programme, interests, relevant competencies, projects, certifications and aspirations.',
                    '- Do not treat missing competencies as automatic evidence that a career is unsuitable.',
                    '- Do not let roles with fewer competency requirements receive artificially higher match scores.',
                    '- Current competency gaps should influence development advice, but Laravel calculates the separate career-readiness score.',
                    '- Match scores are advisory estimates and do not guarantee employment or career success.',
                    '- Give exactly three concise and practical development actions for each recommendation.',
                    '- Development actions must be grounded in the supplied student profile and BIICF competency names.',
                    '- Do not name specific certifications, courses, frameworks, employers, providers or standards unless they are explicitly supplied in the recommendation context.',
                    '- If no named training is supplied, suggest generic actions such as a relevant course, practical project, lab exercise, portfolio activity or supervised experience.',
                    '- Refer to competencies by their names, never by internal competency IDs or requirement numbers.',
                    '- Do not invent achievements, qualifications, competencies or experience for the student.',
                    '- Do not calculate or return detailed competency gaps; Laravel will calculate those from BIICF data.',
                    '- Use UK English.',
                    '- Keep each explanation concise.',
                ]),
            ],

            [
                'role' => 'user',

                'content' =>
                    "CareerPath recommendation context:\n"
                    . $contextJson
                    . "\n\nSelect and rank the student's top three BIICF career recommendations.",
            ],
        ];

        $result = $this->groq->chat(
            $messages,
            $this->responseFormat(
                $eligibleRoleIds
            )
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
                'Groq returned invalid Career Recommendation JSON.',
                previous: $exception
            );
        }

        if (! is_array($decoded)) {
            throw new UnexpectedValueException(
                'Groq returned an invalid Career Recommendation response.'
            );
        }

        $this->validateResponse(
            $decoded,
            $eligibleRoleIds
        );

        return $decoded;
    }

    /**
     * Validate recommendation rules that JSON Schema
     * cannot reliably enforce by itself.
     */
    private function validateResponse(
        array $response,
        array $eligibleRoleIds
    ): void {
        $recommendations =
            $response['recommendations']
            ?? null;

        if (
            ! is_array($recommendations)
            || count($recommendations) !== 3
        ) {
            throw new UnexpectedValueException(
                'Groq must return exactly three Career Recommendations.'
            );
        }

        $roleIds = collect($recommendations)
            ->pluck('biicf_job_role_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($roleIds->unique()->count() !== 3) {
            throw new UnexpectedValueException(
                'Groq returned duplicate BIICF job roles.'
            );
        }

        foreach ($roleIds as $roleId) {
            if (! in_array($roleId, $eligibleRoleIds, true)) {
                throw new UnexpectedValueException(
                    "Groq returned ineligible BIICF job role ID {$roleId}."
                );
            }
        }

        $ranks = collect($recommendations)
            ->pluck('rank')
            ->map(fn ($rank) => (int) $rank)
            ->sort()
            ->values()
            ->all();

        if ($ranks !== [1, 2, 3]) {
            throw new UnexpectedValueException(
                'Groq must return recommendation ranks 1, 2 and 3 exactly once.'
            );
        }
    }

    /**
     * Keep the AI output contract deliberately small.
     *
     * Laravel enriches these recommendations afterwards with
     * matched competencies, proficiency gaps and BIICF labels.
     */
    private function responseFormat(
        array $eligibleRoleIds
    ): array {
        return [
            'type' => 'json_schema',

            'json_schema' => [
                'name' =>
                    'career_recommendation_response',

                'strict' =>
                    true,

                'schema' => [
                    'type' =>
                        'object',

                    'properties' => [
                        'schema_version' => [
                            'type' =>
                                'string',

                            'enum' => [
                                '2.0',
                            ],
                        ],

                        'status' => [
                            'type' =>
                                'string',

                            'enum' => [
                                'completed',
                            ],
                        ],

                        'recommendations' => [
                            'type' =>
                                'array',

                            'minItems' =>
                                3,

                            'maxItems' =>
                                3,

                            'items' => [
                                'type' =>
                                    'object',

                                'properties' => [
                                    'biicf_job_role_id' => [
                                        'type' =>
                                            'integer',

                                        'enum' =>
                                            $eligibleRoleIds,
                                    ],

                                    'rank' => [
                                        'type' =>
                                            'integer',

                                        'minimum' =>
                                            1,

                                        'maximum' =>
                                            3,
                                    ],

                                    'match_score' => [
                                        'type' =>
                                            'number',

                                        'minimum' =>
                                            0,

                                        'maximum' =>
                                            100,
                                    ],

                                    'development_plan' => [
                                        'type' =>
                                            'array',

                                        'minItems' =>
                                            3,

                                        'maxItems' =>
                                            3,

                                        'items' => [
                                            'type' =>
                                                'string',
                                        ],
                                    ],

                                    'explanation' => [
                                        'type' =>
                                            'string',
                                    ],
                                ],

                                'required' => [
                                    'biicf_job_role_id',
                                    'rank',
                                    'match_score',
                                    'development_plan',
                                    'explanation',
                                ],

                                'additionalProperties' =>
                                    false,
                            ],
                        ],
                    ],

                    'required' => [
                        'schema_version',
                        'status',
                        'recommendations',
                    ],

                    'additionalProperties' =>
                        false,
                ],
            ],
        ];
    }
}