<?php

namespace App\Services\AI;

use App\Contracts\CareerAdviserClient;
use JsonException;
use UnexpectedValueException;

class GroqCareerAdviserClient implements CareerAdviserClient
{
    public function __construct(
        private GroqClient $groq
    ) {
    }

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
                    'Your role is to provide personalised ICT career guidance using the student profile, existing career recommendations, and BIICF information supplied by CareerPath BN.',
                    '',
                    'Rules:',
                    '- Base statements about the student only on the supplied CareerPath context.',
                    '- Base BIICF-specific claims only on the supplied BIICF reference data.',
                    '- Do not invent BIICF roles, competencies, scores, qualifications or student information.',
                    '- match_score is the AI-generated estimate of overall career suitability based on the student profile.',
                    '- career_readiness_score is calculated by CareerPath from recorded student competency levels against BIICF role requirements.',
                    '- Never describe a high match score as meaning the student is highly ready for the role.',
                    '- Clearly distinguish career suitability from current competency readiness when discussing both scores.',
                    '- When discussing competency proficiency, copy the supplied competency names, current levels, required levels and BIICF labels exactly.',
                    '- Do not recommend or name specific external certifications, courses, frameworks, employers, providers or standards unless the supplied context explicitly identifies them as relevant BIICF training or certification data.',
                    '- When no named training is supplied, use generic suggestions such as a relevant course, practical project, lab exercise, portfolio activity or supervised experience.',
                    '- Do not invent career progression routes. Only describe progression when the supplied BIICF information explicitly supports it.',
                    '- Avoid mentioning internal BIICF database IDs unless the student specifically asks for them.',
                    '- If the supplied data is insufficient to answer something, clearly say what information is unavailable.',
                    '- When relevant, explain how the student\'s competencies, interests, academic background, projects, certifications, aspirations and current recommendations relate to the question.',
                    '- Give practical and achievable next steps when appropriate.',
                    '- Do not claim that a recommendation guarantees employment or career success.',
                    '- Keep the response focused and easy for a student to understand.',
                    '- Use UK English.',
                ]),
            ],
            [
                'role' => 'user',
                'content' =>
                    "CareerPath context:\n"
                    . $contextJson
                    . "\n\nStudent question:\n"
                    . trim($message),
            ],
        ];

        $result = $this->groq->chat(
            $messages,
            $this->responseFormat()
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