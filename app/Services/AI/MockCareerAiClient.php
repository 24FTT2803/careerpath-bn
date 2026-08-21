<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;

class MockCareerAiClient implements CareerAiClient
{
    /**
     * Return a profile-responsive mock response while
     * the real Career AI API is still under development.
     */
    public function recommend(array $payload): array
    {
        $searchableText = $this->searchableText($payload);
        $scores = $this->scoreCareers($searchableText);

        arsort($scores);

        $rankedCareerIds = array_keys($scores);

        $primaryCareerId = $rankedCareerIds[0] ?? 1;
        $secondaryCareerId = $rankedCareerIds[1] ?? 5;
        $tertiaryCareerId = $rankedCareerIds[2] ?? 3;

        return [
            'schema_version' => '1.0',
            'status' => 'completed',

            'recommendations' => [
                $this->recommendationFor(
                    $primaryCareerId,
                    1
                ),

                $this->recommendationFor(
                    $secondaryCareerId,
                    2
                ),

                $this->recommendationFor(
                    $tertiaryCareerId,
                    3
                ),
            ],
        ];
    }

    /**
     * Convert all values from the student payload into
     * searchable text for the temporary mock rules.
     */
    private function searchableText(array $payload): string
    {
        $values = [];

        array_walk_recursive(
            $payload,
            function ($value) use (&$values) {
                if (
                    is_string($value)
                    || is_numeric($value)
                ) {
                    $values[] = strtolower(
                        (string) $value
                    );
                }
            }
        );

        return implode(' ', $values);
    }

    /**
     * Give each BIICF career a temporary score based on
     * keywords found in the student profile.
     *
     * This is mock behaviour only.
     * Laravel is not intended to perform the real
     * career-matching algorithm.
     */
    private function scoreCareers(string $text): array
    {
        // Small default scores preserve a sensible fallback.
        $scores = [
            1 => 2, // Software Developer
            2 => 0, // Network Engineer
            3 => 0, // Data Analyst
            4 => 0, // Cybersecurity Specialist
            5 => 1, // Cloud Engineer
        ];

        $keywords = [
            // Software Developer
            1 => [
                'application development' => 3,
                'programming' => 6,
                'java' => 5,
                'javascript' => 5,
                'php' => 5,
                'react' => 4,
                'web development' => 4,
                'software developer' => 6,
                'software engineer' => 6,
                'problem solving' => 1,
            ],

            // Network Engineer
            2 => [
                'networking' => 7,
                'network engineer' => 8,
                'linux' => 3,
                'troubleshooting' => 4,
                'ccna' => 6,
                'network infrastructure' => 6,
            ],

            // Data Analyst
            3 => [
                'data analysis' => 8,
                'data analyst' => 8,
                'sql' => 5,
                'python' => 4,
                'analytical thinking' => 5,
                'research' => 3,
                'data analytics' => 7,
            ],

            // Cybersecurity Specialist
            4 => [
                'cybersecurity' => 9,
                'cybersecurity specialist' => 9,
                'security' => 7,
                'linux' => 3,
                'networking' => 3,
                'security+' => 7,
                'comptia security' => 7,
            ],

            // Cloud Engineer
            5 => [
                'cloud computing' => 9,
                'cloud engineer' => 9,
                'aws' => 6,
                'azure' => 6,
                'docker' => 5,
                'cloud' => 6,
                'linux' => 2,
            ],
        ];

        foreach ($keywords as $careerId => $careerKeywords) {
            foreach ($careerKeywords as $keyword => $points) {
                if (str_contains($text, $keyword)) {
                    $scores[$careerId] += $points;
                }
            }
        }

        return $scores;
    }

    /**
     * Build the temporary fake recommendation response
     * for a BIICF career.
     *
     * Competency levels in this mock are illustrative
     * only. They are not authoritative BIICF career
     * requirements.
     */
    private function recommendationFor(
        int $careerId,
        int $rank
    ): array {
        $templates = [
            1 => [
                'primary_match_score' => 91,
                'secondary_match_score' => 74,
                'primary_readiness' => 79,
                'secondary_readiness' => 64,

                'matched_skills' => [
                    'Programming',
                    'Problem Solving',
                    'Application Development',
                ],

                'skill_gaps' => [
                    [
                        'skill_name' => 'Software Testing',
                        'skill_type' => 'technical',
                        'current_level' => 'Assist',
                        'current_level_value' => 2,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 1,
                    ],
                    [
                        'skill_name' => 'Cloud Deployment',
                        'skill_type' => 'technical',
                        'current_level' => 'Follow',
                        'current_level_value' => 1,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 2,
                    ],
                    [
                        'skill_name' => 'Communication',
                        'skill_type' => 'soft',
                        'current_level' => 'Intermediate',
                        'current_level_value' => 2,
                        'recommended_level' => 'Advanced',
                        'required_level' => 3,
                        'required_label' => 'Advanced',
                        'gap' => 1,
                    ],
                ],

                'development_plan' => [
                    'Build more complete software development projects.',
                    'Improve software testing and debugging practices.',
                    'Gain experience deploying applications.',
                ],

                'explanation' =>
                    'The student profile shows strong alignment with software development through programming-related skills, interests, and application development experience.',
            ],

            2 => [
                'primary_match_score' => 90,
                'secondary_match_score' => 73,
                'primary_readiness' => 77,
                'secondary_readiness' => 63,

                'matched_skills' => [
                    'Networking',
                    'Linux',
                    'Technical Troubleshooting',
                ],

                'skill_gaps' => [
                    [
                        'skill_name' => 'Network Configuration',
                        'skill_type' => 'technical',
                        'current_level' => 'Assist',
                        'current_level_value' => 2,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 1,
                    ],
                    [
                        'skill_name' => 'Network Security',
                        'skill_type' => 'technical',
                        'current_level' => 'Follow',
                        'current_level_value' => 1,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 2,
                    ],
                    [
                        'skill_name' => 'Documentation',
                        'skill_type' => 'soft',
                        'current_level' => 'Intermediate',
                        'current_level_value' => 2,
                        'recommended_level' => 'Advanced',
                        'required_level' => 3,
                        'required_label' => 'Advanced',
                        'gap' => 1,
                    ],
                ],

                'development_plan' => [
                    'Practise configuring network devices and services.',
                    'Build stronger Linux administration skills.',
                    'Study networking concepts and troubleshooting techniques.',
                ],

                'explanation' =>
                    'The student profile indicates an interest in networking, infrastructure, Linux, and technical troubleshooting.',
            ],

            3 => [
                'primary_match_score' => 92,
                'secondary_match_score' => 72,
                'primary_readiness' => 81,
                'secondary_readiness' => 62,

                'matched_skills' => [
                    'Data Analysis',
                    'SQL',
                    'Analytical Thinking',
                ],

                'skill_gaps' => [
                    [
                        'skill_name' => 'Data Visualisation',
                        'skill_type' => 'technical',
                        'current_level' => 'Assist',
                        'current_level_value' => 2,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 1,
                    ],
                    [
                        'skill_name' => 'Statistical Analysis',
                        'skill_type' => 'technical',
                        'current_level' => 'Follow',
                        'current_level_value' => 1,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 2,
                    ],
                    [
                        'skill_name' => 'Communication',
                        'skill_type' => 'soft',
                        'current_level' => 'Intermediate',
                        'current_level_value' => 2,
                        'recommended_level' => 'Advanced',
                        'required_level' => 3,
                        'required_label' => 'Advanced',
                        'gap' => 1,
                    ],
                ],

                'development_plan' => [
                    'Practise analysing structured datasets.',
                    'Improve SQL and data visualisation skills.',
                    'Build portfolio projects using real datasets.',
                ],

                'explanation' =>
                    'The student profile shows strong alignment with data analysis through analytical interests, SQL, Python, or data-oriented activities.',
            ],

            4 => [
                'primary_match_score' => 93,
                'secondary_match_score' => 75,
                'primary_readiness' => 80,
                'secondary_readiness' => 65,

                'matched_skills' => [
                    'Cybersecurity',
                    'Networking',
                    'Linux',
                ],

                'skill_gaps' => [
                    [
                        'skill_name' => 'Security Monitoring',
                        'skill_type' => 'technical',
                        'current_level' => 'Assist',
                        'current_level_value' => 2,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 1,
                    ],
                    [
                        'skill_name' => 'Incident Response',
                        'skill_type' => 'technical',
                        'current_level' => 'Follow',
                        'current_level_value' => 1,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 2,
                    ],
                    [
                        'skill_name' => 'Attention to Detail',
                        'skill_type' => 'soft',
                        'current_level' => 'Intermediate',
                        'current_level_value' => 2,
                        'recommended_level' => 'Advanced',
                        'required_level' => 3,
                        'required_label' => 'Advanced',
                        'gap' => 1,
                    ],
                ],

                'development_plan' => [
                    'Practise fundamental cybersecurity techniques.',
                    'Learn security monitoring and incident response.',
                    'Build practical networking and Linux security experience.',
                ],

                'explanation' =>
                    'The student profile indicates strong interest in cybersecurity, networking, Linux, and protecting digital systems.',
            ],

            5 => [
                'primary_match_score' => 94,
                'secondary_match_score' => 76,
                'primary_readiness' => 82,
                'secondary_readiness' => 66,

                'matched_skills' => [
                    'Cloud Computing',
                    'AWS',
                    'Infrastructure',
                ],

                'skill_gaps' => [
                    [
                        'skill_name' => 'Cloud Architecture',
                        'skill_type' => 'technical',
                        'current_level' => 'Assist',
                        'current_level_value' => 2,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 1,
                    ],
                    [
                        'skill_name' => 'Infrastructure Automation',
                        'skill_type' => 'technical',
                        'current_level' => 'Follow',
                        'current_level_value' => 1,
                        'recommended_level' => 'Apply',
                        'required_level' => 3,
                        'required_label' => 'Apply',
                        'gap' => 2,
                    ],
                    [
                        'skill_name' => 'Adaptability',
                        'skill_type' => 'soft',
                        'current_level' => 'Intermediate',
                        'current_level_value' => 2,
                        'recommended_level' => 'Advanced',
                        'required_level' => 3,
                        'required_label' => 'Advanced',
                        'gap' => 1,
                    ],
                ],

                'development_plan' => [
                    'Gain practical experience with AWS or Azure.',
                    'Learn containerisation using Docker.',
                    'Practise deploying and managing cloud infrastructure.',
                ],

                'explanation' =>
                    'The student profile shows strong alignment with cloud engineering through cloud-platform, infrastructure, Linux, or containerisation interests.',
            ],
        ];

        $template = $templates[$careerId]
            ?? $templates[1];

        return [
            'biicf_career_id' => $careerId,
            'rank' => $rank,

            'match_score' => match ($rank) {
                1 => $template['primary_match_score'],
                2 => $template['secondary_match_score'],
                default => max(
                    $template['secondary_match_score'] - 10,
                    0
                ),
            },

            'matched_skills' =>
                $template['matched_skills'],

            'skill_gaps' =>
                $template['skill_gaps'],

            'development_plan' =>
                $template['development_plan'],

            'career_readiness_score' => match ($rank) {
                1 => $template['primary_readiness'],
                2 => $template['secondary_readiness'],
                default => max(
                    $template['secondary_readiness'] - 10,
                    0
                ),
            },

            'explanation' =>
                $template['explanation'],
        ];
    }
}