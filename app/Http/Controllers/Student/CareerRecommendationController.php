<?php

namespace App\Http\Controllers\Student;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\CareerRecommendation;
use App\Models\User;
use App\Services\AI\CareerRecommendationService;
use App\Services\Business\EntitlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class CareerRecommendationController extends Controller
{
    public function __construct(
        private CareerRecommendationService $recommendationService,
        private EntitlementService $entitlements
    ) {
    }

    /**
     * Display the analysis for one of the student's
     * career recommendations.
     */
    public function analysis(
        int $recommendation
    ): View|RedirectResponse {
        /** @var User $student */
        $student = Auth::user();

        $generationAccess =
            $this->entitlements
                ->featureAccess(
                    $student,
                    'career_recommendations.enabled'
                );

        if (! $generationAccess['allowed']) {
            return redirect()
                ->route('student.dashboard')
                ->with(
                    'warning',
                    'Career recommendation generation is unavailable. '
                    . $generationAccess['message']
                );
        }

        abort_unless(
            $student->isStudent(),
            403
        );

        /** @var CareerRecommendation $careerRecommendation */
        $careerRecommendation = $student
            ->careerRecommendations()
            ->with([
                'career',
                'jobRole.subSector',
                'jobRole.entryRequirement',
                'jobRole.trainings',
            ])
            ->findOrFail($recommendation);

        $legacyCareer =
            $careerRecommendation->career;

        $jobRole =
            $careerRecommendation->jobRole;

        /*
         * Present both the legacy recommendation model and
         * the current BIICF job-role model through one
         * consistent structure for the view.
         */
        $careerPresentation = [
            'title' =>
                $jobRole?->title
                ?? $legacyCareer?->job_title
                ?? 'Career',

            'subsector' =>
                $jobRole?->subSector?->name
                ?? $legacyCareer?->subsector
                ?? 'Sub-sector unavailable',

            'job_description' =>
                $jobRole?->job_description
                ?? $legacyCareer?->job_description
                ?? 'Job description is currently unavailable.',
        ];

        if ($jobRole) {
            $entryRequirement =
                $jobRole->entryRequirement;

            $careerDetails = [
                'entry_requirements' =>
                    array_values(
                        array_filter([
                            filled(
                                $entryRequirement?->bdqf_level
                            )
                                ? 'BDQF Level: '
                                    . $entryRequirement->bdqf_level
                                : null,

                            filled(
                                $entryRequirement?->field_of_study
                            )
                                ? 'Field of Study: '
                                    . $entryRequirement->field_of_study
                                : null,

                            filled(
                                $entryRequirement?->alternative_pathway
                            )
                                ? 'Alternative Pathway: '
                                    . $entryRequirement->alternative_pathway
                                : null,

                            filled(
                                $entryRequirement?->years_experience
                            )
                                ? 'Years of Experience: '
                                    . $entryRequirement->years_experience
                                : null,
                        ])
                    ),

                'recommended_training' =>
                    $jobRole->trainings
                        ->map(
                            fn ($training) =>
                                $training->name
                        )
                        ->filter()
                        ->values()
                        ->all(),

                /*
                 * No separate authoritative certification
                 * relationship currently exists for the
                 * current BIICF job-role dataset.
                 */
                'certifications' =>
                    [],
            ];
        } else {
            $careerDetails = [
                'entry_requirements' =>
                    $this->normaliseList(
                        $legacyCareer?->entry_requirements
                    ),

                'recommended_training' =>
                    $this->normaliseList(
                        $legacyCareer?->recommended_training
                    ),

                'certifications' =>
                    $this->normaliseList(
                        $legacyCareer?->certifications
                    ),
            ];
        }

        return view(
            'student.recommendations.analysis',
            compact(
                'careerRecommendation',
                'careerPresentation',
                'careerDetails'
            )
        );
    }

    /**
     * Generate a new set of career recommendations for the student.
     */
    public function generate(): RedirectResponse
    {
        /** @var User $student */
        $student = Auth::user();

        $generationAccess =
            $this->entitlements
                ->featureAccess(
                    $student,
                    'career_recommendations.enabled'
                );

        if (! $generationAccess['allowed']) {
            return redirect()
                ->route('student.dashboard')
                ->with(
                    'warning',
                    'Career recommendation generation is unavailable. '
                    . $generationAccess['message']
                );
        }

        try {
            $recommendations =
                $this->recommendationService
                    ->generateFor($student);

            NotificationHelper::logCareerRecommendation(
                $student->id,
                $student->name,
                $recommendations->count()
            );
        } catch (ConnectionException $exception) {
            report($exception);

            return redirect()
                ->route('student.dashboard')
                ->with(
                    'warning',
                    'CareerPath could not reach the AI service. Please try again shortly.'
                );
        } catch (RequestException $exception) {
            report($exception);

            $message =
                $exception->response->status() === 429
                    ? 'The AI service is currently busy due to usage limits. Please wait a moment and try again.'
                    : 'The Career Recommendation AI is temporarily unavailable. Please try again later.';

            return redirect()
                ->route('student.dashboard')
                ->with(
                    'warning',
                    $message
                );
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('student.dashboard')
                ->with(
                    'warning',
                    'Career recommendations could not be processed. Please try again later.'
                );
        }

        return redirect()
            ->route('student.dashboard')
            ->with(
                'success',
                'Career recommendations generated successfully.'
            );
    }

    /**
     * Convert legacy BIICF list fields into a consistent array.
     */
    private function normaliseList(
        mixed $value
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (
            ! is_string($value)
            || trim($value) === ''
        ) {
            return [];
        }

        $decoded = json_decode(
            $value,
            true
        );

        if (is_array($decoded)) {
            return $decoded;
        }

        if (is_string($decoded)) {
            $decodedAgain = json_decode(
                $decoded,
                true
            );

            if (is_array($decodedAgain)) {
                return $decodedAgain;
            }
        }

        return [$value];
    }
}