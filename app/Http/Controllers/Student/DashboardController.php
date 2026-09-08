<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Business\EntitlementService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private EntitlementService $entitlements
    ) {
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $profileCompletion =
            $user->profile_completion;

        $recommendations = $user
            ->careerRecommendations()
            ->with([
                'career',
                'jobRole.subSector',
            ])
            ->orderBy('rank')
            ->limit(3)
            ->get();

        $recommendationCount =
            $recommendations->count();

        $readinessScore =
            $this->calculateReadinessScore(
                $user
            );

        $milestones =
            $user->milestones;

        $milestoneCount = $milestones
            ->where(
                'is_completed',
                true
            )
            ->count();

        $recentActivities =
            $this->getRecentActivities(
                $user
            );

        /*
         * Features remain visible in the UI even
         * when access is unavailable.
         */
        $careerAdviserAccess =
            $this->entitlements
                ->featureAccess(
                    $user,
                    'career_adviser.enabled'
                );

        $recommendationGenerationAccess =
            $this->entitlements
                ->featureAccess(
                    $user,
                    'career_recommendations.enabled'
                );

        $detailedAnalysisAccess =
            $this->entitlements
                ->featureAccess(
                    $user,
                    'career_recommendations.detailed_analysis.enabled'
                );

        return view(
            'student.dashboard.index',
            compact(
                'user',
                'profileCompletion',
                'recommendations',
                'recommendationCount',
                'readinessScore',
                'milestones',
                'milestoneCount',
                'recentActivities',
                'careerAdviserAccess',
                'recommendationGenerationAccess',
                'detailedAnalysisAccess'
            )
        );
    }

    private function calculateReadinessScore(
        $user
    ) {
        $score = 0;
        $count = 0;

        if ($user->cgpa) {
            $score +=
                ($user->cgpa / 4.0)
                * 30;

            $count++;
        }

        if (
            $user
                ->competencies()
                ->exists()
        ) {
            $score += min(
                $user
                    ->competencies()
                    ->count()
                * 3,
                30
            );

            $count++;
        }

        if (
            $user
                ->certifications()
                ->exists()
        ) {
            $score += min(
                $user
                    ->certifications()
                    ->count()
                * 7,
                20
            );

            $count++;
        }

        if (
            $user
                ->projects()
                ->exists()
        ) {
            $score += min(
                $user
                    ->projects()
                    ->count()
                * 7,
                20
            );

            $count++;
        }

        return $count > 0
            ? round($score)
            : 0;
    }

    private function getRecentActivities(
        $user
    ) {
        $iconMap = [
            'profile' => 'user-edit',
            'career' => 'briefcase',
            'milestone' =>
                'flag-checkered',
        ];

        return $user
            ->notifications()
            ->whereIn(
                'type',
                [
                    'profile',
                    'career',
                    'milestone',
                ]
            )
            ->latest()
            ->limit(5)
            ->get()
            ->map(
                function (
                    $notification
                ) use ($iconMap) {
                    return [
                        'message' =>
                            $notification
                                ->message,

                        'time' =>
                            $notification
                                ->created_at
                                ->diffForHumans(),

                        'icon' =>
                            $iconMap[
                                $notification
                                    ->type
                            ]
                            ?? 'bell',
                    ];
                }
            )
            ->all();
    }
}