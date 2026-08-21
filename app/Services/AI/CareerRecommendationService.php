<?php

namespace App\Services\AI;

use App\Contracts\CareerAiClient;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CareerRecommendationService
{
    public function __construct(
        private CareerAiClient $careerAi,
        private CareerAiPayloadBuilder $payloadBuilder
    ) {
    }

    /**
     * Generate and save the student's current career recommendations.
     */
    public function generateFor(User $student): Collection
    {
        // Build the student profile payload.
        $payload = $this->payloadBuilder->build($student);

        // Send the payload to the configured AI client.
        $response = $this->careerAi->recommend($payload);

        // Validate the AI response before storing it.
        $validated = $this->validateResponse($response);

        // Replace the student's previous recommendation set.
        DB::transaction(function () use ($student, $validated) {
            $student->careerRecommendations()->delete();

            foreach ($validated['recommendations'] as $recommendation) {
                $student->careerRecommendations()->create([
                    'biicf_career_id' =>
                        $recommendation['biicf_career_id'],

                    'rank' =>
                        $recommendation['rank'],

                    'match_score' =>
                        $recommendation['match_score'],

                    'matched_skills' =>
                        $recommendation['matched_skills'],

                    'skill_gaps' =>
                        $recommendation['skill_gaps'],

                    'development_plan' =>
                        $recommendation['development_plan'],

                    'career_readiness_score' =>
                        $recommendation['career_readiness_score'],

                    'explanation' =>
                        $recommendation['explanation'] ?? null,
                ]);
            }
        });

        return $student->careerRecommendations()
            ->with('career')
            ->orderBy('rank')
            ->get();
    }

    /**
     * Validate the structured response returned by the Career AI.
     */
    private function validateResponse(array $response): array
    {
        return Validator::make($response, [
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
        ])->validate();
    }
}