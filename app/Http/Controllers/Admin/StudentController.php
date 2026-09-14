<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Display list of students (Read-Only)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        $query = User::where('role', 'student')
            ->with(['profile', 'competencies', 'milestones']); // Add milestones

        // Filter by programme
        if ($request->has('programme') && $request->programme) {
            $query->where('programme', $request->programme);
        }

        // Search by name or student ID
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('student_id', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(15);

        // Get all programmes for filter
        $programmes = User::where('role', 'student')
            ->distinct()
            ->pluck('programme')
            ->filter()
            ->values();

        // Calculate stats for header
        $totalStudents = User::where('role', 'student')->count();
        $completedProfiles = User::where('role', 'student')->get()
            ->filter(function ($student) {
                return ($student->profile_completion ?? 0) >= 70;
            })->count();
        $completionRate = $totalStudents > 0 ? round(($completedProfiles / $totalStudents) * 100) : 0;
        $atRiskStudents = User::where('role', 'student')->get()
            ->filter(function ($student) {
                return ($student->readiness_score ?? 0) < 40;
            })->count();

        return view('admin.students.index', compact(
            'students',
            'programmes',
            'isAdmin',
            'completionRate',
            'atRiskStudents'
        ));
    }

    /**
     * View single student profile (Read-Only) - WITH ERROR HANDLING
     */
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Load student with relationships
        $student = User::where('role', 'student')
            ->with([
                'profile',
                'academicRecords',
                'competencies',
                'interests',
                'projects',
                'certifications',
                'aspirations',
                'milestones',
            ])
            ->findOrFail($id);

        // ============================================
        // SAFELY LOAD CAREER RECOMMENDATIONS
        // ============================================
        $topRecommendations = collect();
        $readinessScore = 0;
        $skillGaps = [];
        $matchedSkills = [];

        try {
            // Check if table exists first
            $hasTable = DB::table('information_schema.tables')
                ->where('table_schema', env('DB_DATABASE'))
                ->where('table_name', 'career_recommendations')
                ->exists();

            if ($hasTable) {
                // Load career recommendations
                $student->load([
                    'currentRecommendations.career',
                    'currentRecommendations.jobRole.subSector',
                ]);

                $topRecommendations = $student->currentRecommendations()
                    ->with(['career', 'jobRole.subSector'])
                    ->orderBy('match_score', 'desc')
                    ->limit(3)
                    ->get();

                // Get readiness score
                $firstRec = $student->currentRecommendations()->first();
                if ($firstRec) {
                    $readinessScore = $firstRec->career_readiness_score ?? 0;
                    $skillGaps = $firstRec->skill_gaps ?? [];
                    $matchedSkills = $firstRec->matched_skills ?? [];
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error - just use defaults
            $topRecommendations = collect();
            $readinessScore = 0;
            $skillGaps = [];
            $matchedSkills = [];
        }

        /*
         * Staff access is defined by role, not by the student's
         * plan, so no entitlement is consulted here.
         */
        $generations = $student
            ->recommendationGenerations()
            ->orderByDesc('generation_number')
            ->get();

        /*
         * Adviser activity only. Staff are shown that a student
         * uses the adviser and how often, never what was said,
         * so students can speak freely to it.
         */
        $adviserActivity = $student->careerAdviserConversation;

        $student->load('groupMemberships.organisationGroup.type');

        return view('admin.students.show', compact(
            'student',
            'generations',
            'adviserActivity',
            'topRecommendations',
            'readinessScore',
            'skillGaps',
            'matchedSkills',
            'isAdmin'
        ));
    }
}
