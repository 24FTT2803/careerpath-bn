<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AI\CareerRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CareerRecommendationController extends Controller
{
    public function __construct(
        private CareerRecommendationService $recommendationService
    ) {
    }

    /**
     * Generate a new set of career recommendations for the student.
     */
    public function generate(): RedirectResponse
    {
        /** @var User $student */
        $student = Auth::user();

        $this->recommendationService->generateFor($student);

        return redirect()
            ->route('student.dashboard')
            ->with(
                'success',
                'Career recommendations generated successfully.'
            );
    }
}