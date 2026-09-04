<?php

namespace App\Services\AI;

use App\Contracts\CareerAdviserClient;

class MockCareerAdviserClient implements CareerAdviserClient
{
    /**
     * Return deterministic Career Adviser responses
     * while the real AI service is unavailable.
     */
    public function ask(
        array $context,
        string $message
    ): array {
        $normalisedMessage = strtolower(
            trim($message)
        );

        $recommendations =
            $context['career_recommendations']
            ?? [];

        $response = match (true) {
            $this->containsAny(
                $normalisedMessage,
                [
                    'top career',
                    'top match',
                    'best career',
                    'career match',
                ]
            ) => $this->explainTopCareer(
                $recommendations
            ),

            $this->containsAny(
                $normalisedMessage,
                [
                    'skill',
                    'competency',
                    'improve',
                    'gap',
                    'weakness',
                ]
            ) => $this->explainSkillGaps(
                $recommendations
            ),

            $this->containsAny(
                $normalisedMessage,
                [
                    'compare',
                    'difference',
                    'options',
                    'careers',
                ]
            ) => $this->compareCareers(
                $recommendations
            ),

            $this->containsAny(
                $normalisedMessage,
                [
                    'next',
                    'next step',
                    'next steps',
                    'plan',
                    'roadmap',
                    'development plan',
                    'what should i do',
                ]
            ) => $this->nextSteps(
                $recommendations
            ),

            default => $this->answerGeneralQuestion(
                $context,
                $normalisedMessage
            ),
        };

        return [
            'schema_version' => '1.0',
            'status' => 'completed',
            'message' => $response,
        ];
    }

    /**
     * Explain the student's highest-ranked recommendation.
     */
    private function explainTopCareer(
        array $recommendations
    ): string {
        $top = $recommendations[0] ?? null;

        if (! $top) {
            return $this->noRecommendationsMessage();
        }

        $careerName =
            $top['career']['job_title']
            ?? 'your top career';

        $matchScore =
            $top['match_score']
            ?? null;

        $readinessScore =
            $top['career_readiness_score']
            ?? null;

        $explanation =
            $top['explanation']
            ?? null;

        $parts = [
            "{$careerName} is currently your highest-ranked career recommendation.",
        ];

        if ($matchScore !== null) {
            $parts[] =
                "Your recorded match score is "
                . round((float) $matchScore)
                . '%.';
        }

        if ($readinessScore !== null) {
            $parts[] =
                "Your current career readiness score is "
                . round((float) $readinessScore)
                . '%.';
        }

        if (
            is_string($explanation)
            && trim($explanation) !== ''
        ) {
            $parts[] = $explanation;
        }

        $matchedSkills =
            $top['matched_skills']
            ?? [];

        if ($matchedSkills !== []) {
            $parts[] =
                'Your matching strengths include '
                . implode(', ', $matchedSkills)
                . '.';
        }

        return implode(
            ' ',
            $parts
        );
    }

    /**
     * Explain skill gaps from the top recommendation.
     */
    private function explainSkillGaps(
        array $recommendations
    ): string {
        $top = $recommendations[0] ?? null;

        if (! $top) {
            return $this->noRecommendationsMessage();
        }

        $careerName =
            $top['career']['job_title']
            ?? 'your top career';

        $skillGaps =
            $top['skill_gaps']
            ?? [];

        if ($skillGaps === []) {
            return
                "There are currently no skill gaps listed for {$careerName}. "
                . 'You can continue strengthening your existing competencies '
                . 'and practical experience.';
        }

        $gapDescriptions = [];

        foreach ($skillGaps as $gap) {
            $skillName =
                $gap['skill_name']
                ?? null;

            if (! is_string($skillName)) {
                continue;
            }

            $current =
                $gap['current_level']
                ?? null;

            $recommended =
                $gap['recommended_level']
                ?? $gap['required_label']
                ?? null;

            if ($current && $recommended) {
                $gapDescriptions[] =
                    "{$skillName} ({$current} → {$recommended})";
            } else {
                $gapDescriptions[] =
                    $skillName;
            }
        }

        if ($gapDescriptions === []) {
            return
                "Your recommendation for {$careerName} contains development "
                . 'areas, but no detailed competency names are currently available.';
        }

        return
            "For {$careerName}, your current priority development areas are "
            . implode(', ', $gapDescriptions)
            . '. Focus on these areas first because they are linked to '
            . 'your highest-ranked career recommendation.';
    }

    /**
     * Compare the student's current recommendation set.
     */
    private function compareCareers(
        array $recommendations
    ): string {
        if ($recommendations === []) {
            return $this->noRecommendationsMessage();
        }

        if (count($recommendations) === 1) {
            return
                'Only one career recommendation is currently available, so '
                . 'there is not enough information to compare multiple options yet.';
        }

        $summaries = [];

        foreach (
            array_slice(
                $recommendations,
                0,
                3
            ) as $recommendation
        ) {
            $careerName =
                $recommendation['career']['job_title']
                ?? 'Career';

            $matchScore =
                round(
                    (float) (
                        $recommendation['match_score']
                        ?? 0
                    )
                );

            $readiness =
                round(
                    (float) (
                        $recommendation['career_readiness_score']
                        ?? 0
                    )
                );

            $summaries[] =
                "{$careerName}: {$matchScore}% match, "
                . "{$readiness}% readiness";
        }

        return
            'Your current career options compare as follows: '
            . implode('; ', $summaries)
            . '. A higher match score indicates stronger alignment with '
            . 'your current profile, while readiness reflects how prepared '
            . 'you currently are for that career.';
    }

    /**
     * Provide development actions from the top recommendation.
     */
    private function nextSteps(
        array $recommendations
    ): string {
        $top = $recommendations[0] ?? null;

        if (! $top) {
            return $this->noRecommendationsMessage();
        }

        $careerName =
            $top['career']['job_title']
            ?? 'your top career';

        $developmentPlan =
            $top['development_plan']
            ?? [];

        if ($developmentPlan === []) {
            return
                "For {$careerName}, start by reviewing your competency gaps, "
                . 'building relevant practical experience, and exploring '
                . 'appropriate BIICF roles and training opportunities.';
        }

        $steps = [];

        foreach (
            array_values($developmentPlan)
            as $index => $step
        ) {
            if (
                ! is_string($step)
                || trim($step) === ''
            ) {
                continue;
            }

            $steps[] =
                ($index + 1)
                . '. '
                . trim($step);
        }

        return
            "For {$careerName}, your current development plan is: "
            . implode(' ', $steps);
    }

    /**
     * Attempt to answer a general question using BIICF
     * job-role information before falling back to the
     * student's top career recommendation.
     */
    private function answerGeneralQuestion(
        array $context,
        string $message
    ): string {
        $jobRoles =
            $context['biicf_reference']['job_roles']
            ?? [];

        foreach ($jobRoles as $role) {
            $title =
                $role['title']
                ?? null;

            if (
                ! is_string($title)
                || trim($title) === ''
            ) {
                continue;
            }

            if (
                str_contains(
                    $message,
                    strtolower($title)
                )
            ) {
                $description =
                    $role['job_description']
                    ?? null;

                $functionalGroup =
                    $role['functional_group']
                    ?? null;

                $parts = [
                    "{$title} is a BIICF job role.",
                ];

                if (
                    is_string($description)
                    && trim($description) !== ''
                ) {
                    $parts[] =
                        trim($description);
                }

                if (
                    is_string($functionalGroup)
                    && trim($functionalGroup) !== ''
                ) {
                    $parts[] =
                        "It is grouped under {$functionalGroup}.";
                }

                return implode(
                    ' ',
                    $parts
                );
            }
        }

        $recommendations =
            $context['career_recommendations']
            ?? [];

        $top =
            $recommendations[0]
            ?? null;

        if (! $top) {
            return
                'I can help explain BIICF roles, competencies and career '
                . 'development. Generate your career recommendations first '
                . 'for more personalised guidance.';
        }

        $careerName =
            $top['career']['job_title']
            ?? 'your top career recommendation';

        return
            "Your current top recommendation is {$careerName}. "
            . 'You can ask me to explain your top career match, identify '
            . 'skills to improve, compare your career options, explain a '
            . 'BIICF role, or suggest your next development steps.';
    }

    /**
     * Check whether the question contains any supplied keywords.
     */
    private function containsAny(
        string $message,
        array $keywords
    ): bool {
        foreach ($keywords as $keyword) {
            if (
                str_contains(
                    $message,
                    $keyword
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Message used when the student has no recommendations.
     */
    private function noRecommendationsMessage(): string
    {
        return
            'You do not have any career recommendations yet. '
            . 'Complete your student profile and generate your career '
            . 'recommendations first so I can provide personalised guidance.';
    }
}