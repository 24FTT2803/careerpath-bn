<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Profile completion
        $profileCompletion = $user->profile_completion;

        // Career recommendations
        $recommendations = $user->careerRecommendations()
            ->with('career')
            ->orderBy('rank')
            ->get();

        $recommendationCount = $recommendations->count();

        // Readiness score
        $readinessScore = $this->calculateReadinessScore($user);

        // Milestones
        $milestones = $user->milestones;
        $milestoneCount = $milestones->where('is_completed', true)->count();

        // Recent activity
        $recentActivities = $this->getRecentActivities($user);

        return view('student.dashboard.index', compact(
            'user',
            'profileCompletion',
            'recommendations',
            'recommendationCount',
            'readinessScore',
            'milestones',
            'milestoneCount',
            'recentActivities'
        ));
    }

    private function calculateReadinessScore($user)
    {
        $score = 0;
        $count = 0;

        if ($user->cgpa) {
            $score += ($user->cgpa / 4.0) * 30;
            $count++;
        }

        if ($user->competencies()->exists()) {
            $score += min($user->competencies()->count() * 3, 30);
            $count++;
        }

        if ($user->certifications()->exists()) {
            $score += min($user->certifications()->count() * 7, 20);
            $count++;
        }

        if ($user->projects()->exists()) {
            $score += min($user->projects()->count() * 7, 20);
            $count++;
        }

        return $count > 0 ? round($score) : 0;
    }

    private function getRecentActivities($user)
    {
        $activities = [];

        if ($user->updated_at && $user->updated_at->diffInDays(now()) < 7) {
            $activities[] = [
                'message' => 'Updated your profile information',
                'time' => $user->updated_at->diffForHumans()
            ];
        }

        $recentMilestone = $user->milestones()
            ->where('is_completed', true)
            ->orderBy('completed_date', 'desc')
            ->first();

        if ($recentMilestone) {
            $activities[] = [
                'message' => "Completed milestone: {$recentMilestone->title}",
                'time' => $recentMilestone->completed_date->diffForHumans()
            ];
        }

        return $activities;
    }
}