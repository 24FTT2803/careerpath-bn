<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

class CareerRecommendationService
{
    public function __construct(
        private CareerAiClient $careerAi,
        private CareerAiPayloadBuilder $payloadBuilder,
        private CareerRecommendationContextBuilder $contextBuilder,
        private CareerRecommendationEnricher $enricher
    ) {
    }

    /**
     * Generate and save the student's current career recommendations.
     */
    public function generateFor(User $student): Collection
    {
        $driver = config(
            'career-ai.driver',
            'mock'
        );

        /*
         * The real Groq integration needs current BIICF role
         * requirements in addition to the student profile.
         *
         * Legacy/mock clients keep receiving the original
         * profile-only payload.
         */
        $payload = $driver === 'groq'
            ? $this->contextBuilder->build($student)
            : $this->payloadBuilder->build($student);

        $response = $this->careerAi->recommend(
            $payload
        );

        $validated = $driver === 'groq'
            ? $this->validateAndEnrichCurrentResponse(
                $student,
                $response
            )
            : $this->validateLegacyResponse(
                $response
            );

        DB::transaction(function () use (
            $student,
            $validated,
            $driver
        ) {
            $student->careerRecommendations()
                ->delete();

            foreach (
                $validated['recommendations']
                as $recommendation
            ) {
                if ($driver === 'groq') {
                    $student->careerRecommendations()
                        ->create([
                            'biicf_career_id' =>
                                null,

                            'biicf_job_role_id' =>
                                $recommendation[
                                    'biicf_job_role_id'
                                ],

                            'rank' =>
                                $recommendation['rank'],

                            'match_score' =>
                                $recommendation[
                                    'match_score'
                                ],

                            'matched_skills' =>
                                $recommendation[
                                    'matched_skills'
                                ],

                            'skill_gaps' =>
                                $recommendation[
                                    'skill_gaps'
                                ],

                            'development_plan' =>
                                $recommendation[
                                    'development_plan'
                                ],

                            'career_readiness_score' =>
                                $recommendation[
                                    'career_readiness_score'
                                ],

                            'explanation' =>
                                $recommendation[
                                    'explanation'
                                ] ?? null,
                        ]);

                    continue;
                }

                $student->careerRecommendations()
                    ->create([
                        'biicf_career_id' =>
                            $recommendation[
                                'biicf_career_id'
                            ],

                        'biicf_job_role_id' =>
                            null,

                        'rank' =>
                            $recommendation['rank'],

                        'match_score' =>
                            $recommendation[
                                'match_score'
                            ],

                        'matched_skills' =>
                            $recommendation[
                                'matched_skills'
                            ],

                        'skill_gaps' =>
                            $recommendation[
                                'skill_gaps'
                            ],

                        'development_plan' =>
                            $recommendation[
                                'development_plan'
                            ],

                        'career_readiness_score' =>
                            $recommendation[
                                'career_readiness_score'
                            ],

                        'explanation' =>
                            $recommendation[
                                'explanation'
                            ] ?? null,
                    ]);
            }
        });

        $query = $student
            ->careerRecommendations()
            ->orderBy('rank');

        if ($driver === 'groq') {
            return $query
                ->with('jobRole')
                ->get();
        }

        return $query
            ->with('career')
            ->get();
    }

    /**
     * Validate and enrich recommendations generated against
     * the current BIICF job-role dataset.
     */
    private function validateAndEnrichCurrentResponse(
        User $student,
        array $response
    ): array {
        $validated = Validator::make(
            $response,
            [
                'schema_version' => [
                    'required',
                    'string',
                    'in:2.0',
                ],

                'status' => [
                    'required',
                    'in:completed',
                ],

                'recommendations' => [
                    'required',
                    'array',
                    'size:3',
                ],

                'recommendations.*.biicf_job_role_id' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:biicf_job_roles,id',
                ],

                'recommendations.*.rank' => [
                    'required',
                    'integer',
                    'between:1,3',
                    'distinct',
                ],

                'recommendations.*.match_score' => [
                    'required',
                    'numeric',
                    'between:0,100',
                ],

                'recommendations.*.development_plan' => [
                    'required',
                    'array',
                    'size:3',
                ],

                'recommendations.*.development_plan.*' => [
                    'required',
                    'string',
                ],

                'recommendations.*.explanation' => [
                    'required',
                    'string',
                ],
            ]
        )->validate();

        $ranks = collect(
            $validated['recommendations']
        )
            ->pluck('rank')
            ->sort()
            ->values()
            ->all();

        if ($ranks !== [1, 2, 3]) {
            throw ValidationException::withMessages([
                'recommendations' =>
                    'Career Recommendation ranks must contain 1, 2 and 3 exactly once.',
            ]);
        }

        $enriched = collect(
            $validated['recommendations']
        )
            ->map(
                fn (array $recommendation) =>
                    $this->enricher->enrich(
                        $student,
                        $recommendation
                    )
            )
            ->sortBy('rank')
            ->values()
            ->all();

        foreach ($enriched as $recommendation) {
            if (
                ! isset(
                    $recommendation[
                        'career_readiness_score'
                    ]
                )
            ) {
                throw new UnexpectedValueException(
                    'Laravel failed to calculate career readiness.'
                );
            }
        }

        return [
            'schema_version' =>
                $validated['schema_version'],

            'status' =>
                $validated['status'],

            'recommendations' =>
                $enriched,
        ];
    }

    /**
     * Validate the existing legacy/mock Career AI response.
     */
    private function validateLegacyResponse(
        array $response
    ): array {
        return Validator::make(
            $response,
            [
                'schema_version' => [
                    'required',
                    'string',
                ],

                'status' => [
                    'required',
                    'in:completed',
                ],

                'recommendations' => [
                    'required',
                    'array',
                    'size:3',
                ],

                'recommendations.*.biicf_career_id' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:biicf_careers,id',
                ],

                'recommendations.*.rank' => [
                    'required',
                    'integer',
                    'between:1,3',
                    'distinct',
                ],

                'recommendations.*.match_score' => [
                    'required',
                    'numeric',
                    'between:0,100',
                ],

                'recommendations.*.matched_skills' => [
                    'present',
                    'array',
                ],

                'recommendations.*.matched_skills.*' => [
                    'string',
                ],

                'recommendations.*.skill_gaps' => [
                    'present',
                    'array',
                ],

                'recommendations.*.skill_gaps.*.skill_name' => [
                    'required',
                    'string',
                ],

                'recommendations.*.skill_gaps.*.skill_type' => [
                    'required',
                    'in:technical,soft',
                ],

                'recommendations.*.skill_gaps.*.current_level' => [
                    'required',
                    'string',
                ],

                'recommendations.*.skill_gaps.*.current_level_value' => [
                    'required',
                    'integer',
                    'between:1,5',
                ],

                'recommendations.*.skill_gaps.*.recommended_level' => [
                    'required',
                    'string',
                ],

                'recommendations.*.skill_gaps.*.required_level' => [
                    'required',
                    'integer',
                    'between:1,5',
                ],

                'recommendations.*.skill_gaps.*.required_label' => [
                    'required',
                    'string',
                ],

                'recommendations.*.skill_gaps.*.gap' => [
                    'required',
                    'integer',
                    'between:0,4',
                ],

                'recommendations.*.development_plan' => [
                    'present',
                    'array',
                ],

                'recommendations.*.development_plan.*' => [
                    'string',
                ],

                'recommendations.*.career_readiness_score' => [
                    'required',
                    'numeric',
                    'between:0,100',
                ],

                'recommendations.*.explanation' => [
                    'nullable',
                    'string',
                ],
            ]
        )->validate();
    }
}