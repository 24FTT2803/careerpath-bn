<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CareerRecommendation;
use App\Models\StudentMilestone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\RecommendationGeneration;

class DashboardController extends Controller
{
    public function __construct(
        private \App\Services\Lecturer\LecturerScopeResolver $scope
    ) {}

    /**
     * Lecturer dashboard: readiness, competency gaps, at-risk
     * students and recent milestone proof submissions — all
     * scoped to the classes this lecturer teaches.
     *
     * Note: there is currently no lecturer-to-student "advisee" assignment
     * in the data model, so this shows the full student cohort rather than
     * a per-lecturer subset. If advisee scoping is added later (e.g. a
     * lecturer_id column on users/student_profiles), filter the $students
     * query below accordingly.
     */
    public function index()
    {
                /*
         * Scoped to this lecturer's classes. A lecturer with no
         * assignments sees an empty dashboard, which matches the
         * empty state on the students page.
         */
        $students = $this->scope
            ->studentsQueryFor(Auth::user())
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
                /*
         * Same scoping as $students above. Both must agree so
         * the numbers on the dashboard match the list.
         */
        $studentsByProgramme = $this->scope
            ->studentsQueryFor(Auth::user())
            ->selectRaw('programme, COUNT(*) as count')
            ->groupBy('programme')
            ->orderByDesc('count')
            ->get();

        // Common competency gaps across all students' latest recommendations.
        $studentIds = $students->pluck('id')->all();

        $skillGaps = $this->getCommonSkillGaps($studentIds);

        $recentSubmissions = StudentMilestone::whereNotNull('proof_submitted_at')
            ->whereIn('user_id', $studentIds)
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
            /**
     * Common skill gaps across the lecturer's students only.
     *
     * @param  array<int, int>  $studentIds
     * @return array<string, int>
     */
    private function getCommonSkillGaps(array $studentIds)
    {
        if ($studentIds === []) {
            return [];
        }

        try {
            $gaps = [];

            /*
             * Only the newest generation per student.
             *
             * Selected by generation number rather than status,
             * because a generation marked "outdated" is still
             * the student's active set of results.
             *
             * @see User::currentRecommendationGeneration()
             */
            $latestGenerationIds = RecommendationGeneration::query()
                ->whereIn('user_id', $studentIds)
                ->selectRaw('MAX(id) as id')
                ->groupBy('user_id')
                ->pluck('id');

            $recommendations = CareerRecommendation::whereNotNull('skill_gaps')
                ->whereIn('recommendation_generation_id', $latestGenerationIds)
                ->get();

            /*
             * Count unique students per skill, not occurrences.
             *
             * A student whose current recommendations list the
             * same gap three times should count as one student,
             * not three. The dedup key pairs the skill with the
             * student's ID, and $seen is declared outside the
             * loop so it persists across every recommendation.
             */
            $seen = [];

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

                    if (! is_string($skillName) && ! is_numeric($skillName)) {
                        continue;
                    }

                    $skillName = (string) $skillName;
                    $key = $skillName . '|' . $rec->user_id;

                    if (isset($seen[$key])) {
                        continue;
                    }

                    $seen[$key] = true;
                    $gaps[$skillName] = ($gaps[$skillName] ?? 0) + 1;
                }
            }

            arsort($gaps);

            return array_slice($gaps, 0, 10);
        } catch (\Exception $e) {
            return [];
        }
    }
}