<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AI\RecommendationStatusService;
use App\Services\Business\EntitlementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function __construct(
        private EntitlementService $entitlements,
        private RecommendationStatusService $recommendationStatus
    ) {}

    /**
     * Show the student's archive of AI activity.
     */
    public function index(): View
    {
        /** @var User $student */
        $student = Auth::user();

        abort_unless(
            $student && $student->isStudent(),
            403
        );

        $recommendationHistoryAccess = $this->entitlements
            ->featureAccess(
                $student,
                'recommendation_history.enabled'
            );

        $adviserHistoryAccess = $this->entitlements
            ->featureAccess(
                $student,
                'career_adviser.history.enabled'
            );

        $reportAccess = $this->entitlements
            ->featureAccess(
                $student,
                'career_recommendations.download.enabled'
            );

        /*
         * Status is otherwise only recalculated when a profile
         * is saved. Refreshing here means the badges on this
         * page are never stale, whatever route the student took
         * to reach it.
         */
        $this->recommendationStatus->refreshFor($student);

        $generations = $recommendationHistoryAccess['allowed']
            ? $student
                ->recommendationGenerations()
                ->with('profileSnapshot')
                ->orderByDesc('generation_number')
                ->get()
            : collect();

        $conversation = $adviserHistoryAccess['allowed']
            ? $student->careerAdviserConversation
            : null;

        return view(
            'student.history.index',
            compact(
                'student',
                'generations',
                'conversation',
                'recommendationHistoryAccess',
                'adviserHistoryAccess',
                'reportAccess'
            )
        );
    }
}
