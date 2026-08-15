<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;

class MockCareerAiClient implements CareerAiClient
{
    /**
     * Return a temporary mock response while the real Career AI API
     * is still under development.
     */
    public function recommend(array $payload): array
    {
        return [
            'schema_version' => '1.0',
            'status' => 'completed',

            'recommendations' => [
                [
                    'biicf_career_id' => 1,
                    'rank' => 1,
                    'match_score' => 88,

                    'matched_skills' => [
                        'Programming',
                        'Problem Solving',
                        'Web Development',
                    ],

                    'skill_gaps' => [
                        [
                            'skill_name' => 'Software Testing',
                            'current_level' => 'beginner',
                            'recommended_level' => 'intermediate',
                        ],
                        [
                            'skill_name' => 'Cloud Computing',
                            'current_level' => 'beginner',
                            'recommended_level' => 'intermediate',
                        ],
                    ],

                    'development_plan' => [
                        'Improve software testing knowledge.',
                        'Build additional full-stack development projects.',
                        'Gain experience deploying applications to cloud platforms.',
                    ],

                    'career_readiness_score' => 76,

                    'explanation' =>
                        'The student profile shows strong alignment with software development based on their interests, competencies, academic background, and preferred work activities.',
                ],

                [
                    'biicf_career_id' => 5,
                    'rank' => 2,
                    'match_score' => 73,

                    'matched_skills' => [
                        'Problem Solving',
                        'Technical Skills',
                    ],

                    'skill_gaps' => [
                        [
                            'skill_name' => 'Cloud Platforms',
                            'current_level' => 'beginner',
                            'recommended_level' => 'intermediate',
                        ],
                    ],

                    'development_plan' => [
                        'Learn cloud platform fundamentals.',
                        'Gain practical experience with AWS or Azure.',
                    ],

                    'career_readiness_score' => 61,

                    'explanation' =>
                        'The student may also be suited to cloud engineering, although additional cloud-specific competencies would be required.',
                ],
            ],
        ];
    }
}