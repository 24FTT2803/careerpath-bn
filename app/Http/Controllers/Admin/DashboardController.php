<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BIICFCareer;
use App\Models\CareerRecommendation;
use App\Models\StudentCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Statistics
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'total_lecturers' => User::where('role', 'lecturer')->count(),
            'total_careers' => BIICFCareer::count(),
            'total_recommendations' => CareerRecommendation::count(),
            'avg_readiness' => round(CareerRecommendation::avg('career_readiness_score') ?? 0, 1),
        ];

        // Students by programme
        $studentsByProgramme = User::where('role', 'student')
            ->selectRaw('programme, COUNT(*) as count')
            ->groupBy('programme')
            ->get();

        // Top career matches
        $topCareers = CareerRecommendation::selectRaw('biicf_career_id, AVG(match_score) as avg_score')
            ->groupBy('biicf_career_id')
            ->with('career')
            ->orderBy('avg_score', 'desc')
            ->limit(5)
            ->get();

        // Common competency gaps
        $skillGaps = $this->getCommonSkillGaps();

        // Recent students
        $recentStudents = User::where('role', 'student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'studentsByProgramme',
            'topCareers',
            'skillGaps',
            'recentStudents',
            'isAdmin'
        ));
    }

    private function getCommonSkillGaps()
    {
        $gaps = [];
        $recommendations = CareerRecommendation::whereNotNull('skill_gaps')->get();

        foreach ($recommendations as $rec) {
            $skillGaps = $rec->skill_gaps ?? [];
            if (is_array($skillGaps)) {
                foreach ($skillGaps as $skill) {
                    $gaps[$skill] = ($gaps[$skill] ?? 0) + 1;
                }
            }
        }

        arsort($gaps);
        return array_slice($gaps, 0, 10);
    }
}