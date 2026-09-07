<?php

namespace App\Services\AI;

use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\User;
use RuntimeException;

class CareerRecommendationContextBuilder
{
    public function __construct(
        private CareerAiPayloadBuilder $profileBuilder
    ) {
    }

    /**
     * Build a compact student and BIICF context for
     * real career recommendation generation.
     */
    public function build(User $student): array
    {
        $profilePayload = $this->profileBuilder->build(
            $student
        );

        $proficiencyLevels = BiicfProficiencyLevel::query()
            ->orderBy('level_number')
            ->get([
                'id',
                'level_number',
                'name',
            ])
            ->keyBy('id');

        $roles = BiicfJobRole::query()
            ->whereHas('competencies')
            ->with([
                'subSector:id,name',

                'competencies:id,name,type',
            ])
            ->orderBy('id')
            ->get([
                'id',
                'sub_sector_id',
                'title',
                'career_path_level',
            ]);

        if ($roles->count() < 3) {
            throw new RuntimeException(
                'At least three competency-profiled BIICF job roles are required.'
            );
        }

        /*
         * Store each BIICF competency once instead of repeating
         * its name and type inside every job role.
         *
         * Format:
         * competency_id => [name, type]
         */
        $competencyCatalogue = $roles
            ->flatMap(
                fn ($role) => $role->competencies
            )
            ->unique('id')
            ->sortBy('id')
            ->mapWithKeys(
                fn ($competency) => [
                    (string) $competency->id => [
                        $competency->name,
                        $competency->type,
                    ],
                ]
            )
            ->all();

        /*
         * Each role stores only compact competency requirement
         * pairs:
         *
         * [competency_id, required_level]
         */
        $eligibleRoles = $roles
            ->map(function ($role) use ($proficiencyLevels) {
                $requirements = $role->competencies
                    ->map(function ($competency) use (
                        $proficiencyLevels,
                        $role
                    ) {
                        $level = $proficiencyLevels->get(
                            $competency->pivot->proficiency_level_id
                        );

                        if (! $level) {
                            throw new RuntimeException(
                                "BIICF job role {$role->id} references "
                                . 'an unknown proficiency level.'
                            );
                        }

                        return [
                            (int) $competency->id,
                            (int) $level->level_number,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'id' =>
                        $role->id,

                    'title' =>
                        $role->title,

                    'sub_sector' =>
                        $role->subSector?->name,

                    'career_path_level' =>
                        (int) $role->career_path_level,

                    'requirements' =>
                        $requirements,
                ];
            })
            ->values()
            ->all();

        return [
            'schema_version' => '2.0',

            'student_profile' =>
                $profilePayload['student_profile'] ?? [],

            'eligible_roles' =>
                $eligibleRoles,

            'reference' => [
                'eligible_role_count' =>
                    count($eligibleRoles),

                'competency_catalogue' =>
                    $competencyCatalogue,

                'proficiency_scale' =>
                    $proficiencyLevels
                        ->values()
                        ->mapWithKeys(
                            fn ($level) => [
                                (string) $level->level_number =>
                                    $level->name,
                            ]
                        )
                        ->all(),
            ],
        ];
    }
}