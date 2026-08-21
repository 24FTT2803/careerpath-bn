<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CareerRecommendation;
use App\Models\User;
use App\Services\AI\CareerRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CareerRecommendationController extends Controller
{
    public function __construct(
        private CareerRecommendationService $recommendationService
    ) {
    }

    /**
     * Display the student's top three career recommendations.
     */
    public function assessment(): View
    {
        /** @var User $student */
        $student = Auth::user();

        abort_unless(
            $student->isStudent(),
            403
        );

        $recommendations = $student
            ->careerRecommendations()
            ->with('career')
            ->orderBy('rank')
            ->limit(3)
            ->get();

        return view(
            'student.recommendations.assessment',
            compact('recommendations')
        );
    }

    /**
     * Display the analysis for one of the student's
     * career recommendations.
     */
    public function analysis(
        int $recommendation
    ): View {
        /** @var User $student */
        $student = Auth::user();

        abort_unless(
            $student->isStudent(),
            403
        );

        /** @var CareerRecommendation $careerRecommendation */
        $careerRecommendation = $student
            ->careerRecommendations()
            ->with('career')
            ->findOrFail($recommendation);

        $career = $careerRecommendation->career;

        $careerDetails = [
            'entry_requirements' => $this->normaliseList(
                $career?->entry_requirements
            ),

            'recommended_training' => $this->normaliseList(
                $career?->recommended_training
            ),

            'certifications' => $this->normaliseList(
                $career?->certifications
            ),
        ];

        return view(
            'student.recommendations.analysis',
            compact(
                'careerRecommendation',
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

        try {
            $this->recommendationService->generateFor($student);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('student.dashboard')
                ->with(
                    'warning',
                    'Career recommendations could not be generated. Please try again later.'
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
     * Convert BIICF list fields into a consistent array.
     *
     * This also supports the currently seeded values that
     * were JSON encoded before being stored in JSON columns.
     */
    private function normaliseList(mixed $value): array
    {
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