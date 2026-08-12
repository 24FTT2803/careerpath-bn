<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Profile completion
        $profileCompletion = $this->calculateProfileCompletion($user);

        // Recommendations (placeholder - Developer 2 will provide)
        $recommendations = collect([]);
        $recommendationCount = 0;

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

    private function calculateProfileCompletion($user)
    {
        $completed = 0;
        $total = 0;

        $sections = [
            'profile' => $user->profile && $user->profile->profile_complete,
            'academic' => $user->academicRecords()->exists(),
            'competencies' => $user->competencies()->exists(),
            'interests' => $user->interests()->exists(),
            'projects' => $user->projects()->exists(),
            'certifications' => $user->certifications()->exists(),
            'aspirations' => $user->aspirations()->exists(),
        ];

        foreach ($sections as $completed_flag) {
            $total++;
            if ($completed_flag) {
                $completed++;
            }
        }

        return $total > 0 ? round(($completed / $total) * 100) : 0;
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