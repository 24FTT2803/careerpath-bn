<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BIICFCareer;
use App\Models\CareerRecommendation;
use App\Models\Notification;
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
            ->with(['profile', 'competencies', 'milestones'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Students for stats
        $students = User::where('role', 'student')->get();

        // Calculate completion rates
        $completedProfiles = $students->filter(function($student) {
            return ($student->profile_completion ?? 0) >= 70;
        })->count();

        $completionRate = $students->count() > 0 
            ? round(($completedProfiles / $students->count()) * 100) 
            : 0;

        // At risk students (readiness < 40%)
        $atRiskStudents = $students->filter(function($student) {
            return ($student->readiness_score ?? 0) < 40;
        })->count();

        // ============================================
        // DYNAMIC RECENT ACTIVITIES FROM NOTIFICATIONS
        // ============================================
        $recentActivities = Notification::getDashboardActivities(10);

        return view('admin.dashboard.index', compact(
            'stats',
            'studentsByProgramme',
            'topCareers',
            'skillGaps',
            'recentStudents',
            'isAdmin',
            'students',
            'completedProfiles',
            'completionRate',
            'atRiskStudents',
            'recentActivities'
        ));
    }

    /**
     * Get common skill gaps across all students
     */
    private function getCommonSkillGaps()
    {
        try {
            $gaps = [];
            $recommendations = CareerRecommendation::whereNotNull('skill_gaps')->get();

            foreach ($recommendations as $rec) {
                $skillGaps = $rec->skill_gaps;
                
                if (is_string($skillGaps)) {
                    $skillGaps = json_decode($skillGaps, true);
                }
                
                if (!is_array($skillGaps) || empty($skillGaps)) {
                    continue;
                }

                foreach ($skillGaps as $gap) {
                    if (is_array($gap) && isset($gap['skill_name'])) {
                        $skillName = $gap['skill_name'];
                    } elseif (is_string($gap)) {
                        $skillName = $gap;
                    } else {
                        continue;
                    }
                    
                    if (is_string($skillName) || is_numeric($skillName)) {
                        $skillName = (string) $skillName;
                        $gaps[$skillName] = ($gaps[$skillName] ?? 0) + 1;
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