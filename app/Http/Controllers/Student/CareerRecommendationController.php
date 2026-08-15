<?php

namespace App\Http\Controllers\Student;

use App\Contracts\CareerAiClient;
use App\Http\Controllers\Controller;
use App\Services\AI\CareerAiPayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CareerRecommendationController extends Controller
{
    public function __construct(
        private CareerAiClient $careerAi,
        private CareerAiPayloadBuilder $payloadBuilder
    ) {
    }

    /**
     * Generate a new set of career recommendations for the student.
     */
    public function generate(): RedirectResponse
    {
        $student = Auth::user();

        // 1. Build the student data payload.
        $payload = $this->payloadBuilder->build($student);

        // 2. Send it through the configured AI client.
        $response = $this->careerAi->recommend($payload);

        // 3. Validate the AI response before trusting or storing it.
        $validated = Validator::make($response, [
            'schema_version' => ['required', 'string'],
            'status' => ['required', 'in:completed'],

            'recommendations' => ['required', 'array', 'min:1'],

            'recommendations.*.biicf_career_id' => [
                'required',
                'integer',
                'exists:biicf_careers,id',
            ],

            'recommendations.*.rank' => [
                'required',
                'integer',
                'min:1',
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

            'recommendations.*.skill_gaps.*.current_level' => [
                'nullable',
                'string',
            ],

            'recommendations.*.skill_gaps.*.recommended_level' => [
                'nullable',
                'string',
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

        // 4. Replace the student's previous recommendation set.
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

        return redirect()
            ->route('student.dashboard')
            ->with(
                'success',
                'Career recommendations generated successfully.'
            );
    }
}