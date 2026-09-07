<?php

namespace App\Services\AI;

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use App\Models\User;

class CareerAdviserContextBuilder
{
    public function __construct(
        private CareerAiPayloadBuilder $profileBuilder
    ) {
    }

    /**
     * Build the CareerPath context available to the Career Adviser.
     */
    public function build(User $student): array
    {
        /*
         * Reuse the Career AI profile builder so both
         * recommendation and Adviser features receive the
         * same representation of the student's profile.
         */
        $profilePayload = $this->profileBuilder->build(
            $student
        );

        /*
         * Support both:
         * - legacy BIICFCareer recommendations
         * - current BiicfJobRole recommendations
         */
        $student->loadMissing([
            'careerRecommendations.career',
            'careerRecommendations.jobRole.subSector',
        ]);

        $recommendations = $student
            ->careerRecommendations
            ->sortBy('rank')
            ->values()
            ->map(function ($recommendation) {
                $legacyCareer =
                    $recommendation->career;

                $jobRole =
                    $recommendation->jobRole;

                return [
                    'biicf_career_id' =>
                        $recommendation->biicf_career_id,

                    'biicf_job_role_id' =>
                        $recommendation->biicf_job_role_id,

                    'rank' =>
                        $recommendation->rank,

                    'match_score' =>
                        $recommendation->match_score,

                    'career_readiness_score' =>
                        $recommendation->career_readiness_score,

                    'matched_skills' =>
                        $recommendation->matched_skills ?? [],

                    'skill_gap_count' =>
                        count(
                            $recommendation->skill_gaps ?? []
                        ),

                    'skill_gaps' =>
                        array_slice(
                            $recommendation->skill_gaps ?? [],
                            0,
                            5
                        ),

                    'development_plan' =>
                        $recommendation->development_plan ?? [],

                    'explanation' =>
                        $recommendation->explanation,

                    /*
                     * Legacy five-role recommendation details.
                     */
                    'career' => $legacyCareer
                        ? [
                            'job_title' =>
                                $legacyCareer->job_title,

                            'subsector' =>
                                $legacyCareer->subsector,

                            'technical_skills' =>
                                $this->normaliseArray(
                                    $legacyCareer->technical_skills
                                ),

                            'soft_skills' =>
                                $this->normaliseArray(
                                    $legacyCareer->soft_skills
                                ),

                            'entry_requirements' =>
                                $this->normaliseArray(
                                    $legacyCareer->entry_requirements
                                ),

                            'recommended_training' =>
                                $this->normaliseArray(
                                    $legacyCareer->recommended_training
                                ),

                            'certifications' =>
                                $this->normaliseArray(
                                    $legacyCareer->certifications
                                ),

                            'job_description' =>
                                $legacyCareer->job_description,

                            'demand_level' =>
                                $legacyCareer->demand_level,
                        ]
                        : null,

                    /*
                     * Current BIICF Explorer recommendation details.
                     */
                    'job_role' => $jobRole
                        ? [
                            'id' =>
                                $jobRole->id,

                            'title' =>
                                $jobRole->title,

                            'sub_sector' =>
                                $jobRole->subSector?->name,

                            'functional_group' =>
                                $jobRole->functional_group,

                            'job_description' =>
                                $jobRole->job_description,

                            'career_path_level' =>
                                $jobRole->career_path_level,
                        ]
                        : null,
                ];
            })
            ->all();

        /*
         * Current BIICF Explorer data is provided as the
         * authoritative reference catalogue available to
         * the Career Adviser.
         */
        $jobRoles = BiicfJobRole::query()
            ->with('subSector:id,name')
            ->orderBy('title')
            ->get([
                'id',
                'sub_sector_id',
                'title',
                'job_description',
                'career_path_level',
            ])
            ->map(fn ($role) => [
                'id' =>
                    $role->id,

                'title' =>
                    $role->title,

                'sub_sector' =>
                    $role->subSector?->name,

                'job_description' =>
                    $role->job_description,

                'career_path_level' =>
                    $role->career_path_level,
            ])
            ->all();

        $competencies = BiicfCompetency::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'type',
                'description',
            ])
            ->map(fn ($competency) => [
                'id' =>
                    $competency->id,

                'name' =>
                    $competency->name,

                'type' =>
                    $competency->type,

                'description' =>
                    $competency->description,
            ])
            ->all();

        return [
            'schema_version' =>
                '1.0',

            'student_profile' =>
                $profilePayload['student_profile'] ?? [],

            'career_recommendations' =>
                $recommendations,

            'biicf_reference' => [
                'job_role_count' =>
                    count($jobRoles),

                'sub_sector_count' =>
                    BiicfSubSector::count(),

                'competency_count' =>
                    count($competencies),

                'job_roles' =>
                    $jobRoles,

                'competencies' =>
                    $competencies,
            ],
        ];
    }

    /**
     * Ensure legacy JSON fields are represented as arrays
     * in the Career Adviser context.
     */
    private function normaliseArray(
        mixed $value
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (
            is_string($value)
            && trim($value) !== ''
        ) {
            $decoded = json_decode(
                $value,
                true
            );

            if (is_array($decoded)) {
                return $decoded;
            }

            return [$value];
        }

        return [];
    }
}