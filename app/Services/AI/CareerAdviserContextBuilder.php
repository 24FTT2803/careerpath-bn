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
         * Reuse the existing Career AI profile builder so the
         * recommendation system and Career Adviser receive the
         * same representation of the student's profile.
         */
        $profilePayload = $this->profileBuilder->build(
            $student
        );

        $student->loadMissing([
            'careerRecommendations.career',
        ]);

        $recommendations = $student
            ->careerRecommendations
            ->sortBy('rank')
            ->values()
            ->map(function ($recommendation) {
                $career = $recommendation->career;

                return [
                    'biicf_career_id' =>
                        $recommendation->biicf_career_id,

                    'rank' =>
                        $recommendation->rank,

                    'match_score' =>
                        $recommendation->match_score,

                    'career_readiness_score' =>
                        $recommendation->career_readiness_score,

                    'matched_skills' =>
                        $recommendation->matched_skills ?? [],

                    'skill_gaps' =>
                        $recommendation->skill_gaps ?? [],

                    'development_plan' =>
                        $recommendation->development_plan ?? [],

                    'explanation' =>
                        $recommendation->explanation,

                    /*
                     * Current recommendation records use the
                     * legacy BIICFCareer model. Keep this
                     * information separate from the newer
                     * BIICF Explorer job-role catalogue.
                     */
                    'career' => $career
                        ? [
                            'job_title' =>
                                $career->job_title,

                            'subsector' =>
                                $career->subsector,

                            'technical_skills' =>
                                $this->normaliseArray(
                                    $career->technical_skills
                                ),

                            'soft_skills' =>
                                $this->normaliseArray(
                                    $career->soft_skills
                                ),

                            'entry_requirements' =>
                                $this->normaliseArray(
                                    $career->entry_requirements
                                ),

                            'recommended_training' =>
                                $this->normaliseArray(
                                    $career->recommended_training
                                ),

                            'certifications' =>
                                $this->normaliseArray(
                                    $career->certifications
                                ),

                            'job_description' =>
                                $career->job_description,

                            'demand_level' =>
                                $career->demand_level,
                        ]
                        : null,
                ];
            })
            ->all();

        /*
         * BIICF Explorer data is supplied as a separate reference
         * catalogue. We deliberately do not assume that a legacy
         * BIICFCareer record maps directly to a BiicfJobRole.
         */
        $jobRoles = BiicfJobRole::query()
            ->orderBy('title')
            ->get([
                'id',
                'sub_sector_id',
                'title',
                'slug',
                'functional_group',
                'job_description',
                'career_path_level',
            ])
            ->map(fn ($role) => [
                'id' =>
                    $role->id,

                'sub_sector_id' =>
                    $role->sub_sector_id,

                'title' =>
                    $role->title,

                'slug' =>
                    $role->slug,

                'functional_group' =>
                    $role->functional_group,

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
                'slug',
                'type',
                'description',
            ])
            ->map(fn ($competency) => [
                'id' =>
                    $competency->id,

                'name' =>
                    $competency->name,

                'slug' =>
                    $competency->slug,

                'type' =>
                    $competency->type,

                'description' =>
                    $competency->description,
            ])
            ->all();

        return [
            'schema_version' => '1.0',

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
    private function normaliseArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
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