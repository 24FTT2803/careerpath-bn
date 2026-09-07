<?php

namespace App\Services\AI;

use App\Models\BiicfJobRole;
use App\Models\User;
use UnexpectedValueException;

class CareerRecommendationEnricher
{
    private const PROFICIENCY_LEVELS = [
        'follow' => 1,
        'assist' => 2,
        'apply' => 3,
        'ensure' => 4,
        'strategise' => 5,
    ];

    private const PROFICIENCY_LABELS = [
        1 => 'Follow',
        2 => 'Assist',
        3 => 'Apply',
        4 => 'Ensure',
        5 => 'Strategise',
    ];

    /**
     * Add deterministic BIICF competency analysis to one
     * AI-selected recommendation.
     */
    public function enrich(
        User $student,
        array $recommendation
    ): array {
        $roleId =
            $recommendation['biicf_job_role_id']
            ?? null;

        if (! is_numeric($roleId)) {
            throw new UnexpectedValueException(
                'Career Recommendation is missing a valid BIICF job role ID.'
            );
        }

        $role = BiicfJobRole::query()
            ->with('competencies:id,name,type')
            ->find($roleId);

        if (! $role) {
            throw new UnexpectedValueException(
                "BIICF job role {$roleId} could not be found."
            );
        }

        $studentCompetencies = $student
            ->competencies()
            ->get([
                'skill_name',
                'category',
                'proficiency_level',
            ])
            ->keyBy(
                fn ($competency) =>
                    $this->normaliseName($competency->skill_name)
            );

        $matchedSkills = [];
        $skillGaps = [];

        $attainmentTotal = 0.0;
        $requirementCount = 0;

        foreach ($role->competencies as $competency) {
            $requiredLevel =
                (int) $competency->pivot->proficiency_level_id;

            if (
                $requiredLevel < 1
                || $requiredLevel > 5
            ) {
                throw new UnexpectedValueException(
                    "BIICF competency {$competency->id} has an invalid required proficiency level."
                );
            }

            $studentCompetency = $studentCompetencies->get(
                $this->normaliseName($competency->name)
            );

            $currentLevel = $studentCompetency
                ? $this->proficiencyValue(
                    $studentCompetency->proficiency_level
                )
                : 0;

            if ($studentCompetency) {
                $matchedSkills[] =
                    $competency->name;
            }

            $gap = max(
                $requiredLevel - $currentLevel,
                0
            );

            if ($gap > 0) {
                $skillGaps[] = [
                    'skill_name' =>
                        $competency->name,

                    'skill_type' =>
                        $competency->type === 'soft_skill'
                            ? 'soft'
                            : 'technical',

                    'current_level' =>
                        $currentLevel > 0
                            ? self::PROFICIENCY_LABELS[$currentLevel]
                            : 'Not recorded',

                    'current_level_value' =>
                        $currentLevel,

                    'recommended_level' =>
                        self::PROFICIENCY_LABELS[$requiredLevel],

                    'required_level' =>
                        $requiredLevel,

                    'required_label' =>
                        self::PROFICIENCY_LABELS[$requiredLevel],

                    'gap' =>
                        $gap,

                    'is_core' =>
                        (bool) $competency->pivot->is_core,
                ];
            }

            $attainmentTotal += min(
                $currentLevel / $requiredLevel,
                1
            );

            $requirementCount++;
        }

        /*
         * Highest-priority gaps first:
         * core competencies, then larger gaps,
         * then technical competencies.
         */
        usort(
            $skillGaps,
            function (array $left, array $right): int {
                return [
                    $right['is_core'] ? 1 : 0,
                    $right['gap'],
                    $right['skill_type'] === 'technical' ? 1 : 0,
                ] <=> [
                    $left['is_core'] ? 1 : 0,
                    $left['gap'],
                    $left['skill_type'] === 'technical' ? 1 : 0,
                ];
            }
        );

        /*
         * is_core is useful while prioritising but is not part
         * of the existing Career Recommendation UI contract.
         */
        $skillGaps = array_map(
            function (array $gap): array {
                unset($gap['is_core']);

                return $gap;
            },
            $skillGaps
        );

        $readinessScore =
            $requirementCount > 0
                ? round(
                    ($attainmentTotal / $requirementCount) * 100,
                    2
                )
                : 0.0;

        return [
            ...$recommendation,

            'matched_skills' =>
                array_values(
                    array_unique($matchedSkills)
                ),

            'skill_gaps' =>
                $skillGaps,

            'career_readiness_score' =>
                $readinessScore,
        ];
    }

    private function proficiencyValue(
        ?string $label
    ): int {
        if (! $label) {
            return 0;
        }

        return self::PROFICIENCY_LEVELS[
            strtolower(trim($label))
        ] ?? 0;
    }

    private function normaliseName(
        string $name
    ): string {
        return strtolower(
            trim($name)
        );
    }
}