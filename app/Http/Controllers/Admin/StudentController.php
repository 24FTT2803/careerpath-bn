<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\CareerRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->with(['profile', 'competencies']);

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

        return view('admin.students.index', compact('students', 'programmes', 'isAdmin'));
    }

    /**
     * View single student profile (Read-Only)
     */
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        $student = User::where('role', 'student')
            ->with([
                'profile',
                'academicRecords',
                'competencies',
                'interests',
                'projects',
                'certifications',
                'aspirations',
                'careerRecommendations.career',
                'milestones'
            ])
            ->findOrFail($id);

        // Get top recommendations
        $topRecommendations = $student->careerRecommendations()
            ->with('career')
            ->orderBy('match_score', 'desc')
            ->limit(3)
            ->get();

        // Calculate readiness score
        $readinessScore = $student->careerRecommendations()
            ->first()
            ->career_readiness_score ?? 0;

        // Get competency gaps
        $skillGaps = [];
        $recommendation = $student->careerRecommendations()->first();
        if ($recommendation) {
            $skillGaps = $recommendation->skill_gaps ?? [];
        }

        // Get matched skills
        $matchedSkills = [];
        if ($recommendation) {
            $matchedSkills = $recommendation->matched_skills ?? [];
        }

        return view('admin.students.show', compact(
            'student',
            'topRecommendations',
            'readinessScore',
            'skillGaps',
            'matchedSkills',
            'isAdmin'
        ));
    }

    /**
     * Export student data (Optional)
     */
    public function export()
    {
        // This would export student data as CSV
        // For future implementation
    }
}