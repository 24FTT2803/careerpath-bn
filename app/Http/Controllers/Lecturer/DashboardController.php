<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CareerRecommendation;
use App\Models\StudentMilestone;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Lecturer dashboard: cohort-wide readiness, common competency gaps,
     * at-risk students, and recent milestone proof submissions to review.
     *
     * Note: there is currently no lecturer-to-student "advisee" assignment
     * in the data model, so this shows the full student cohort rather than
     * a per-lecturer subset. If advisee scoping is added later (e.g. a
     * lecturer_id column on users/student_profiles), filter the $students
     * query below accordingly.
     */
    public function index()
    {
        $students = User::where('role', 'student')
            ->with(['profile'])
            ->get();

        $totalStudents = $students->count();

        // Average readiness score (computed accessor, so this is done in PHP).
        $avgReadiness = $totalStudents > 0
            ? round($students->avg(fn ($s) => $s->readiness_score), 1)
            : 0;

        // Profile completion rate (>= 70% counts as "complete").
        $completedProfiles = $students->filter(
            fn ($s) => ($s->profile_completion ?? 0) >= 70
        )->count();

        $completionRate = $totalStudents > 0
            ? round(($completedProfiles / $totalStudents) * 100)
            : 0;

        // At-risk students: readiness below 40%, sorted lowest first.
        $atRiskStudents = $students
            ->filter(fn ($s) => ($s->readiness_score ?? 0) < 40)
            ->sortBy(fn ($s) => $s->readiness_score ?? 0)
            ->take(10)
            ->values();

        // Students grouped by programme.
        $studentsByProgramme = User::where('role', 'student')
            ->selectRaw('programme, COUNT(*) as count')
            ->groupBy('programme')
            ->orderByDesc('count')
            ->get();

        // Common competency gaps across all students' latest recommendations.
        $skillGaps = $this->getCommonSkillGaps();

        // Recent milestone proof submissions awaiting lecturer review.
        $recentSubmissions = StudentMilestone::whereNotNull('proof_submitted_at')
            ->with('user:id,first_name,last_name,name,programme')
            ->orderByDesc('proof_submitted_at')
            ->limit(8)
            ->get();

        return view('lecturer.dashboard', compact(
            'totalStudents',
            'avgReadiness',
            'completionRate',
            'atRiskStudents',
            'studentsByProgramme',
            'skillGaps',
            'recentSubmissions'
        ));
    }

    /**
     * Get common skill gaps across all students (same approach as the admin
     * dashboard's equivalent, kept independent here so lecturer/admin views
     * can evolve separately without risk of one breaking the other).
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

                if (! is_array($skillGaps) || empty($skillGaps)) {
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
