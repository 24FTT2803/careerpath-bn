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
    ) {}

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $profileCompletion =
            $user->profile_completion;

        $recommendations = $user
            ->currentRecommendations()
            ->with([
                'career',
                'jobRole.subSector',
            ])
            ->orderBy('rank')
            ->limit(3)
            ->get();

        $recommendationCount =
            $recommendations->count();

        /*
         * How far short the student is for their best match.
         * The old tile counted the recommendations themselves,
         * which is always three and tells a student nothing.
         */
        $competencyGapCount = collect(
            $recommendations->first()?->skill_gaps ?? []
        )
            ->filter(fn ($gap) => filled($gap))
            ->count();

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
         * What to do next, in one step rather than four tiles of
         * numbers. A student arriving from the sign-up form has
         * never been told where to start, and a student who
         * finishes their profile is never told that the
         * recommendations are generated on request.
         */
        $nextStep = $this->nextStep($user, $profileCompletion, $recommendationCount);

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
                'competencyGapCount',
                'readinessScore',
                'milestones',
                'milestoneCount',
                'recentActivities',
                'detailedAnalysisAccess',
                'nextStep'
            )
        );
    }

    /**
     * The one thing worth doing next, or nothing.
     *
     * Three states, in the order a student meets them: a profile
     * to finish, recommendations never generated, and then
     * nothing — a dashboard that keeps telling someone to do
     * something they have done is a dashboard they stop reading.
     *
     * @return array<string, mixed>|null
     */
    private function nextStep(User $user, $profileCompletion, int $recommendationCount): ?array
    {
        if ((int) $profileCompletion < 100) {
            $waiting = $user->unfinishedProfileSections();

            return [
                'key' => 'profile',
                'title' => 'Finish your profile',
                'body' => $waiting === []
                    ? 'A little more to fill in before your recommendations can be any good.'
                    : 'Still to fill in: '.implode(', ', $waiting).'.',
                'action' => 'Update my profile',
                'route' => route('student.profile.edit'),
                'icon' => 'fa-user-pen',
            ];
        }

        if ($recommendationCount === 0) {
            return [
                'key' => 'generate',
                'title' => 'Your profile is ready',
                'body' => 'Generate your career recommendations to see which roles in the '
                    .'framework match what you have recorded.',
                'action' => 'Generate recommendations',
                'route' => route('student.recommendations.index'),
                'icon' => 'fa-wand-magic-sparkles',
            ];
        }

        return null;
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
            'milestone' => 'flag-checkered',
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
                        'message' => $notification
                            ->message,

                        'time' => $notification
                            ->created_at
                            ->diffForHumans(),

                        'icon' => $iconMap[
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
