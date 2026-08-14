<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BIICFCareer;
use App\Models\CareerRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // ============================================
        // SAFE QUERIES WITH TRY-CATCH
        // ============================================

        // Total students - always works
        $totalStudents = User::where('role', 'student')->count();

        // Total lecturers - always works
        $totalLecturers = User::where('role', 'lecturer')->count();

        // Career-related queries - may fail if tables don't exist
        $totalCareers = 0;
        $totalRecommendations = 0;
        $avgReadiness = 0;
        $topCareers = collect();
        $skillGaps = [];

        try {
            // Check if biicf_careers table exists
            $hasCareersTable = DB::table('information_schema.tables')
                ->where('table_schema', env('DB_DATABASE'))
                ->where('table_name', 'biicf_careers')
                ->exists();

            if ($hasCareersTable) {
                $totalCareers = BIICFCareer::count() ?? 0;
            }
        } catch (\Exception $e) {
            $totalCareers = 0;
        }

        try {
            // Check if career_recommendations table exists
            $hasRecommendationsTable = DB::table('information_schema.tables')
                ->where('table_schema', env('DB_DATABASE'))
                ->where('table_name', 'career_recommendations')
                ->exists();

            if ($hasRecommendationsTable) {
                $totalRecommendations = CareerRecommendation::count() ?? 0;
                $avgReadiness = round(CareerRecommendation::avg('career_readiness_score') ?? 0, 1);

                // Top career matches
                if ($totalRecommendations > 0) {
                    $topCareers = CareerRecommendation::selectRaw('biicf_career_id, AVG(match_score) as avg_score')
                        ->groupBy('biicf_career_id')
                        ->with('career')
                        ->orderBy('avg_score', 'desc')
                        ->limit(5)
                        ->get();
                }

                // Common competency gaps
                $skillGaps = $this->getCommonSkillGaps();
            }
        } catch (\Exception $e) {
            $totalRecommendations = 0;
            $avgReadiness = 0;
            $topCareers = collect();
            $skillGaps = [];
        }

        $stats = [
            'total_students' => $totalStudents,
            'total_lecturers' => $totalLecturers,
            'total_careers' => $totalCareers,
            'total_recommendations' => $totalRecommendations,
            'avg_readiness' => $avgReadiness,
            'has_careers' => $totalCareers > 0,
            'has_recommendations' => $totalRecommendations > 0,
        ];

        // Students by programme - always works
        $studentsByProgramme = User::where('role', 'student')
            ->selectRaw('programme, COUNT(*) as count')
            ->groupBy('programme')
            ->get();

        // Recent students - always works
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
        try {
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
        } catch (\Exception $e) {
            return [];
        }
    }
}