<?php

namespace App\Http\Controllers\Student;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\CareerRecommendation;
use App\Models\RecommendationGeneration;
use App\Models\User;
use App\Services\AI\CareerRecommendationService;
use App\Services\AI\CareerReportBuilder;
use App\Services\AI\RegenerationCooldown;
use App\Services\Business\EntitlementService;
use App\Services\Business\FeatureUsageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerRecommendationController extends Controller
{
    public function __construct(
        private CareerRecommendationService $recommendationService,
        private EntitlementService $entitlements,
        private FeatureUsageService $featureUsage,
        private RegenerationCooldown $cooldown
    ) {}

    /**
     * Display the student's career recommendations.
     */
    public function index(): View
    {
        /** @var User $student */
        $student = Auth::user();

        abort_unless(
            $student && $student->isStudent(),
            403
        );

        $generationAccess =
            $this->entitlements
                ->featureAccess(
                    $student,
                    'career_recommendations.enabled'
                );

        $detailedAnalysisAccess = $this->entitlements->featureAccess(
            $student,
            'career_recommendations.detailed_analysis.enabled'
        );

        $generationQuota =
            $this->featureUsage
                ->status(
                    $student,
                    'career_recommendations.generation_quota'
                );

        $recommendations =
            $student
                ->currentRecommendations()
                ->with([
                    'career',
                    'jobRole.subSector',
                ])
                ->orderBy('rank')
                ->orderByDesc('match_score')
                ->get();

        $currentGeneration = $student
            ->currentRecommendationGeneration()
            ->first();

        /*
         * Shown as a countdown on the button, so the pause is
         * visible rather than only enforced after a click.
         */
        $cooldownSeconds = $this->cooldown
            ->secondsRemaining($student);

        return view(
            'student.recommendations.index',
            compact(
                'student',
                'recommendations',
                'currentGeneration',
                'generationAccess',
                'detailedAnalysisAccess',
                'generationQuota',
                'cooldownSeconds'
            )
        );
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

        $detailedAnalysisAccess =
            $this->entitlements
                ->featureAccess(
                    $student,
                    'career_recommendations.detailed_analysis.enabled'
                );

        if (! $detailedAnalysisAccess['allowed']) {
            return redirect()
                ->route('student.recommendations.index')
                ->with(
                    'warning',
                    'Detailed career analysis is unavailable. '
                    .$detailedAnalysisAccess['message']
                );
        }

        abort_unless(
            $student->isStudent(),
            403
        );

        /** @var CareerRecommendation $careerRecommendation */
        $careerRecommendation = $student
            ->currentRecommendations()
            ->with([
                'career',
                'jobRole.subSector',
                'jobRole.descriptionPoints',
                'jobRole.entryRequirement',
                'jobRole.trainings',
                'jobRole.programmingLanguages',
            ])
            ->findOrFail($recommendation);

        $legacyCareer =
            $careerRecommendation->career;

        $jobRole =
            $careerRecommendation->jobRole;

        $description = $jobRole?->descriptionPoints
            ->pluck('text')->filter()->implode("\n\n");

        if (blank($description)) {
            $description = $jobRole?->job_description
                ?: $legacyCareer?->job_description;
        }

        $analysisSections = [];

        foreach ([
            'match_score',
            'matched_competencies',
            'competency_gaps',
            'entry_requirements',
            'certification_suggestions',
            'training_suggestions',
            'development_roadmap',
        ] as $section) {
            $analysisSections[$section] = $this->entitlements->allows(
                $student,
                "career_recommendations.detailed_analysis.{$section}.enabled"
            );
        }

        /*
         * Present both the legacy recommendation model and
         * the current BIICF job-role model through one
         * consistent structure for the view.
         */
        $careerPresentation = [
            'title' => $jobRole?->title
                ?? $legacyCareer?->job_title
                ?? 'Career',

            'subsector' => $jobRole?->subSector?->name
                ?? $legacyCareer?->subsector
                ?? 'Sub-sector unavailable',

            'job_description' => filled($description)
                ? $description
                : 'Job description is currently unavailable.',
        ];

        if ($jobRole) {
            $entryRequirement =
                $jobRole->entryRequirement;

            /*
             * Already reads as "BDQF Level 3", or as the book's
             * own wording where that names no single level, so it
             * needs no label of its own.
             */
            $entryLevel = $entryRequirement?->level_label;

            $careerDetails = [
                'entry_requirements' => array_values(
                    array_filter([
                        filled($entryLevel)
                            ? $entryLevel
                            : null,

                        filled(
                            $entryRequirement?->field_of_study
                        )
                            ? 'Field of Study: '
                                .$entryRequirement->field_of_study
                            : null,

                        filled(
                            $entryRequirement?->alternative_pathway
                        )
                            ? 'Alternative Pathway: '
                                .$entryRequirement->alternative_pathway
                            : null,

                        filled(
                            $entryRequirement?->years_experience
                        )
                            ? 'Years of Experience: '
                                .$entryRequirement->years_experience
                            : null,
                    ])
                ),

                'programming_languages' => $jobRole->programmingLanguages
                    ->pluck('name')->filter()->values()->all(),

                'recommended_training' => $jobRole->trainings
                    ->map(
                        fn ($training) => $training->name
                    )
                    ->filter()
                    ->values()
                    ->all(),

                /*
                 * No separate authoritative certification
                 * relationship currently exists for the
                 * current BIICF job-role dataset.
                 */
                'certifications' => [],
            ];
        } else {
            $careerDetails = [
                'entry_requirements' => $this->normaliseList(
                    $legacyCareer?->entry_requirements
                ),

                'programming_languages' => [],

                'recommended_training' => $this->normaliseList(
                    $legacyCareer?->recommended_training
                ),

                'certifications' => $this->normaliseList(
                    $legacyCareer?->certifications
                ),
            ];
        }

        return view(
            'student.recommendations.analysis',
            compact(
                'careerRecommendation',
                'careerPresentation',
                'careerDetails',
                'analysisSections'
            )
        );
    }

    /**
     * Download the career report for one generation.
     *
     * The document is the same one the profile export produces.
     * Passing a generation renders it as it stood then, so an
     * archived report never shows newer recommendations than the
     * ones it was produced from.
     */
    public function report(
        RecommendationGeneration $generation,
        CareerReportBuilder $reportBuilder
    ) {
        /** @var User $student */
        $student = Auth::user();

        abort_unless(
            $student && $student->isStudent(),
            403
        );

        /*
         * 404 rather than 403 so one student cannot confirm
         * which generations another student has.
         */
        abort_unless(
            $generation->user_id === $student->id,
            404
        );

        $downloadAccess = $this->entitlements
            ->featureAccess(
                $student,
                'career_recommendations.download.enabled'
            );

        abort_unless(
            $downloadAccess['allowed'],
            $downloadAccess['reason'] === 'maintenance'
                ? 503
                : 403,
            $downloadAccess['message']
                ?? 'Report downloads are unavailable.'
        );

        $pdf = Pdf::loadView(
            'student.profile.export',
            $reportBuilder->for($student, $generation)
        )->setPaper('a4', 'portrait');

        return $pdf->download(
            'CareerPath-BN-'
            .Str::slug($student->name)
            .'-Report-'
            .$generation->generation_number
            .'.pdf'
        );
    }

    /**
     * Generate a new set of career recommendations for the student.
     */
    public function generate(): RedirectResponse
    {
        /** @var User $student */
        $student = Auth::user();

        /*
        * Feature availability always takes precedence
        * over quota enforcement.
        */
        $generationAccess =
            $this->entitlements
                ->featureAccess(
                    $student,
                    'career_recommendations.enabled'
                );

        if (! $generationAccess['allowed']) {
            return redirect()
                ->route('student.recommendations.index')
                ->with(
                    'warning',
                    'Career recommendation generation is unavailable. '
                    .$generationAccess['message']
                );
        }

        /*
        * The pause comes before the quota, so a double press
        * costs the student nothing. Spending a quota use on a
        * request they did not mean to make would be the worst
        * outcome of the two.
        */
        if ($this->cooldown->blocks($student)) {
            return redirect()
                ->route('student.recommendations.index')
                ->with(
                    'warning',
                    $this->cooldown->message($student)
                );
        }

        /*
        * Quota is checked only when the parent feature
        * itself is available.
        */
        $quotaStatus =
            $this->featureUsage
                ->status(
                    $student,
                    'career_recommendations.generation_quota'
                );

        if (! $quotaStatus['allowed']) {
            return redirect()
                ->route('student.recommendations.index')
                ->with(
                    'warning',
                    'Career recommendation generation is unavailable. '
                    .$quotaStatus['message']
                );
        }

        try {
            $recommendations =
                $this->recommendationService
                    ->generateFor($student);

            /*
            * A quota use is consumed only after recommendation
            * generation has completed successfully.
            */
            $this->featureUsage
                ->recordUsage(
                    $student,
                    'career_recommendations.generation_quota'
                );

            NotificationHelper::logCareerRecommendation(
                $student->id,
                $student->name,
                $recommendations->count()
            );
        } catch (ConnectionException $exception) {
            report($exception);

            return redirect()
                ->route('student.recommendations.index')
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
                ->route('student.recommendations.index')
                ->with(
                    'warning',
                    $message
                );
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('student.recommendations.index')
                ->with(
                    'warning',
                    'Career recommendations could not be processed. Please try again later.'
                );
        }

        /*
         * Says the page is arriving with something new on it, so
         * the matches can announce themselves. A student presses
         * generate, the page reloads looking exactly as it did,
         * and the thing they asked for is below the fold — which
         * is how someone comes away thinking nothing happened.
         */
        return redirect()
            ->route('student.recommendations.index')
            ->with('just_generated', true);
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
