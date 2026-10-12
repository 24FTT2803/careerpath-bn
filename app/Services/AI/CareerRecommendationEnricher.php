<?php

namespace App\Services\AI;

use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\User;
use UnexpectedValueException;

class CareerRecommendationEnricher
{
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

        /*
         * A role's required proficiencies belong to its tiers,
         * one column of the book's table per tier. What a student
         * is measured against is the lowest tier: the least that
         * gets them into the role at all.
         */
        $role = BiicfJobRole::query()
            ->with(['tiers.competencies:id,name,type'])
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

        /*
         * proficiency_level_id is a key, not a level. The two
         * happened to agree while there was a single five-rung
         * scale and nothing else in the table; with the soft
         * skill scale alongside it they do not, so the level has
         * to be looked up rather than assumed.
         *
         * The scale is looked up with it, because a number means
         * nothing on its own here. Advanced is the third of a
         * soft skill's four rungs and Apply is the third of a
         * technical competency's five, and they are not the same
         * requirement — nor is either the same fraction of what
         * the job asks for.
         */
        $levels = BiicfProficiencyLevel::all();

        $rungs = $levels->keyBy('id');

        /* What each rung is called, within its own scale. */
        $named = $levels
            ->groupBy('scale')
            ->map(fn ($scale) => $scale->pluck('name', 'level_number'));

        /* And the way back, for reading what a student recorded. */
        $numbered = $named->map(
            fn ($byNumber) => $byNumber->mapWithKeys(
                fn (string $name, int $number) => [mb_strtolower(trim($name)) => $number]
            )
        );

        $entryCompetencies = $role->tiers->first()?->competencies
            ?? collect();

        $matchedSkills = [];
        $skillGaps = [];

        $attainmentTotal = 0.0;
        $requirementCount = 0;

        foreach ($entryCompetencies as $competency) {
            $rung = $rungs->get($competency->pivot->proficiency_level_id);

            $requiredLevel = (int) ($rung?->level_number ?? 0);

            /*
             * Checked against the length of its own scale. The
             * old ceiling of five was the technical scale's, and
             * a soft skill can only reach four — so nothing a
             * soft skill could get wrong was ever caught by it.
             */
            if ($rung === null || $requiredLevel < 1 || $requiredLevel > $rung->scaleLength()) {
                throw new UnexpectedValueException(
                    "BIICF competency {$competency->id} has an invalid required proficiency level."
                );
            }

            $scale = $rung->scale;

            $studentCompetency = $studentCompetencies->get(
                $this->normaliseName($competency->name)
            );

            /*
             * Read on the scale the competency is rated on. A
             * student who recorded a soft skill as "Advanced"
             * used to come out at nothing at all, because the
             * only words this knew were the technical ones —
             * so every soft skill they had was reported as a
             * gap they did not have.
             */
            $currentLevel = $studentCompetency
                ? $this->proficiencyValue(
                    $studentCompetency->proficiency_level,
                    $numbered->get($scale)?->all() ?? []
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
                            ? ($named->get($scale)[$currentLevel] ?? (string) $currentLevel)
                            : 'Not recorded',

                    'current_level_value' =>
                        $currentLevel,

                    'recommended_level' =>
                        $named->get($scale)[$requiredLevel] ?? (string) $requiredLevel,

                    'required_level' =>
                        $requiredLevel,

                    'required_label' =>
                        $named->get($scale)[$requiredLevel] ?? (string) $requiredLevel,

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

    /**
     * What a recorded label is worth, on one named scale.
     *
     * A label only means something within its own scale, so the
     * scale is passed in rather than assumed: "Advanced" is a
     * soft skill's third rung and means nothing at all against
     * the technical one.
     *
     * @param  array<string, int>  $scale
     */
    private function proficiencyValue(
        ?string $label,
        array $scale
    ): int {
        if (! $label) {
            return 0;
        }

        return $scale[mb_strtolower(trim($label))] ?? 0;
    }

    private function normaliseName(
        string $name
    ): string {
        return strtolower(
            trim($name)
        );
    }
}