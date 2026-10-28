<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;
use App\Models\RecommendationGeneration;
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
        private CareerRecommendationEnricher $enricher,
        private ProfileSnapshotService $profileSnapshots
    ) {}

    /**
     * Generate a new recommendation generation for the student.
     *
     * Previous generations are preserved. The newest generation
     * becomes current and any earlier current generation becomes
     * previous.
     */
    public function generateFor(User $student): Collection
    {
        $driver = config(
            'career-ai.driver',
            'mock'
        );

        $current = $this->speaksCurrentSchema($driver);

        /*
         * A client answering in schema 2.0 needs the current BIICF
         * role requirements as well as the student profile; one
         * answering in 1.0 is choosing between the five legacy
         * careers and gets the profile on its own.
         */
        $payload = $current
            ? $this->contextBuilder->build($student)
            : $this->payloadBuilder->build($student);

        $response = $this->careerAi->recommend(
            $payload
        );

        $validated = $current
            ? $this->validateAndEnrichCurrentResponse(
                $student,
                $response
            )
            : $this->validateLegacyResponse(
                $response
            );

        /*
         * Nothing is written until the AI response has been
         * validated, so a failed generation leaves the student's
         * existing current generation untouched.
         */
        $generation = DB::transaction(function () use (
            $student,
            $validated,
            $driver,
            $current
        ): RecommendationGeneration {
            $generation = $this->openGeneration(
                $student,
                $validated,
                $driver
            );

            foreach (
                $validated['recommendations'] as $recommendation
            ) {
                $generation->recommendations()->create(
                    $this->recommendationAttributes(
                        $student,
                        $recommendation,
                        $current
                    )
                );
            }

            return $generation;
        });

        $query = $generation->recommendations();

        if ($current) {
            return $query
                ->with('jobRole')
                ->get();
        }

        return $query
            ->with('career')
            ->get();
    }

    /**
     * Whether the configured driver answers in schema 2.0.
     *
     * Unknown drivers are treated as legacy, which is the safe
     * way round: a 1.0 response checked against the 2.0 rules is
     * refused outright, where the reverse would store a
     * recommendation pointing at nothing.
     */
    private function speaksCurrentSchema(string $driver): bool
    {
        return config(
            "career-ai.schemas.{$driver}",
            '1.0'
        ) === '2.0';
    }

    /**
     * Supersede the student's current generation and open a new
     * one for the incoming recommendations.
     *
     * @param  array<string, mixed>  $validated
     */
    private function openGeneration(
        User $student,
        array $validated,
        string $driver
    ): RecommendationGeneration {
        $student->recommendationGenerations()
            ->where(
                'status',
                RecommendationGeneration::STATUS_CURRENT
            )
            ->update([
                'status' => RecommendationGeneration::STATUS_PREVIOUS,
            ]);

        $nextNumber = (int) $student
            ->recommendationGenerations()
            ->max('generation_number') + 1;

        /*
         * Captured only once the AI response has been validated,
         * so a failed generation never records a snapshot.
         */
        $snapshot = $this->profileSnapshots->captureFor(
            $student
        );

        return $student->recommendationGenerations()->create([
            'profile_snapshot_id' => $snapshot->id,

            'generation_number' => $nextNumber,

            'status' => RecommendationGeneration::STATUS_CURRENT,

            'driver' => $driver,

            'schema_version' => $validated['schema_version'] ?? null,

            /*
             * Stored rather than derived because the number of
             * recommendations per generation is plan-configurable
             * and may differ between generations.
             */
            'recommendation_count' => count($validated['recommendations']),

            'generated_at' => now(),
        ]);
    }

    /**
     * Map a validated AI recommendation onto database columns.
     *
     * user_id is written alongside the generation so existing
     * per-student queries keep working.
     *
     * @param  array<string, mixed>  $recommendation
     * @return array<string, mixed>
     */
    private function recommendationAttributes(
        User $student,
        array $recommendation,
        bool $isCurrentSchema
    ): array {
        return [
            'user_id' => $student->id,

            'biicf_career_id' => $isCurrentSchema
                ? null
                : $recommendation['biicf_career_id'],

            'biicf_job_role_id' => $isCurrentSchema
                ? $recommendation['biicf_job_role_id']
                : null,

            'rank' => $recommendation['rank'],

            'match_score' => $recommendation['match_score'],

            'matched_skills' => $recommendation['matched_skills'],

            'skill_gaps' => $recommendation['skill_gaps'],

            'development_plan' => $recommendation['development_plan'],

            'career_readiness_score' => $recommendation['career_readiness_score'],

            'explanation' => $recommendation['explanation'] ?? null,
        ];
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
                'recommendations' => 'Career Recommendation ranks must contain 1, 2 and 3 exactly once.',
            ]);
        }

        $enriched = collect(
            $validated['recommendations']
        )
            ->map(
                fn (array $recommendation) => $this->enricher->enrich(
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
            'schema_version' => $validated['schema_version'],

            'status' => $validated['status'],

            'recommendations' => $enriched,
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
