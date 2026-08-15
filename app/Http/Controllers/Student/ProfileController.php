<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\AcademicRecord;
use App\Models\StudentCompetency;
use App\Models\StudentInterest;
use App\Models\StudentProject;
use App\Models\StudentCertification;
use App\Models\StudentAspiration;
use App\Models\Notification;
use App\Models\StudentMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AI\CareerRecommendationService;

class ProfileController extends Controller
{
    public function __construct(
        private CareerRecommendationService $recommendationService
    ) {
    }

    /**
     * Display the student's profile.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $user->load([
            'profile',
            'academicRecords',
            'competencies',
            'interests',
            'projects',
            'certifications',
            'aspirations'
        ]);

        $profileCompletion = $user->profile_completion;

        return view('student.profile.index', compact('user', 'profileCompletion'));
    }

    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();

        $user->load([
            'profile',
            'academicRecords',
            'competencies',
            'interests',
            'projects',
            'certifications',
            'aspirations'
        ]);

        $profileCompletion = $user->profile_completion;

        return view('student.profile.edit', compact('user', 'profileCompletion'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'programme' => ['required', 'string', 'max:255'],
            'cgpa' => ['required', 'numeric', 'min:0', 'max:4'],
            'skills' => ['nullable', 'array'],
            'interests' => ['nullable', 'array'],
            'projects_text' => ['nullable', 'string'],
            'certifications_text' => ['nullable', 'string'],
            'career_goals_text' => ['nullable', 'string'],
            'vision_statement' => ['nullable', 'string', 'max:500'],
            'long_term_goals' => ['nullable', 'string', 'max:500'],
        ]);

        // Update User
        $user->update([
            'name' => $request->name,
            'programme' => $request->programme,
            'cgpa' => $request->cgpa,
        ]);

        // Update Profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'nationality' => $request->nationality,
                'bio' => $request->bio,
            ]
        );

        // Process Skills
        if ($request->has('skills')) {
            $this->syncSkills($user, $request->skills);
        }

        // Process Interests
        if ($request->has('interests')) {
            $this->syncInterests($user, $request->interests);
        }

        // Process Projects (comma separated)
        if ($request->filled('projects_text')) {
            $projectTitles = array_filter(array_map('trim', explode(',', $request->projects_text)));
            $this->syncProjects($user, $projectTitles);
        } else {
            $user->projects()->delete();
        }

        // Process Certifications (comma separated)
        if ($request->filled('certifications_text')) {
            $certNames = array_filter(array_map('trim', explode(',', $request->certifications_text)));
            $this->syncCertifications($user, $certNames);
        } else {
            $user->certifications()->delete();
        }

        // Update Aspirations
        $user->aspirations()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'career_goals' => $request->filled('career_goals_text')
                    ? [$request->career_goals_text]
                    : [],
                'vision_statement' => $request->vision_statement,
                'long_term_goals' => $request->long_term_goals,
            ]
        );

        // Update profile completion
        $completionPercentage = $user->profile_completion;
        $profileIsComplete = $completionPercentage >= 70;

        $user->profile()->update([
            'completion_percentage' => $completionPercentage,
            'profile_complete' => $profileIsComplete,
        ]);

        // Generate the student's first career recommendations
        // once their profile reaches the existing completion threshold.
        if (
            $profileIsComplete
            && ! $user->careerRecommendations()->exists()
        ) {
            $user->refresh();

            $this->recommendationService->generateFor($user);
        }

        return redirect()->route('student.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // ============================================
    // SETTINGS METHODS
    // ============================================

    /**
     * Show settings page.
     */
    public function settings()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('student.settings.index', compact('user'));
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('student.settings')
            ->with('success', 'Password updated successfully!');
    }

    // ============================================
    // NOTIFICATION METHODS
    // ============================================

    /**
     * Show notifications page.
     */
    public function notifications()
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = $user->unreadNotifications()->count();

        return view('student.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    // ============================================
    // MILESTONE METHODS
    // ============================================

    /**
     * Show milestones page.
     */
    public function milestones()
    {
        /** @var User $user */
        $user = Auth::user();

        $milestones = $user->milestones()
            ->orderBy('is_completed')
            ->orderBy('target_date')
            ->get();

        return view('student.milestones.index', compact('milestones'));
    }

    /**
     * Store a new milestone.
     */
    public function storeMilestone(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:academic,career,personal,skill'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date', 'after:today'],
        ]);

        $milestone = StudentMilestone::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'target_date' => $request->target_date,
        ]);

        return redirect()->route('student.milestones')
            ->with('success', 'Milestone added successfully!');
    }

    /**
     * Complete a milestone.
     */
    public function completeMilestone($id)
    {
        $milestone = StudentMilestone::where('user_id', Auth::id())
            ->findOrFail($id);

        $milestone->update([
            'is_completed' => true,
            'completed_date' => now(),
        ]);

        return redirect()->route('student.milestones')
            ->with('success', '🎉 Milestone completed!');
    }

    /**
     * Delete a milestone.
     */
    public function destroyMilestone($id)
    {
        $milestone = StudentMilestone::where('user_id', Auth::id())
            ->findOrFail($id);

        $milestone->delete();

        return redirect()->route('student.milestones')
            ->with('success', 'Milestone deleted.');
    }

    // ============================================
    // SYNC METHODS
    // ============================================

    /**
     * Sync skills/competencies.
     */
    private function syncSkills(User $user, array $skills)
    {
        $user->competencies()->delete();

        foreach ($skills as $skill) {
            $user->competencies()->create([
                'skill_name' => $skill,
                'category' => 'technical',
                'proficiency_level' => 'intermediate',
            ]);
        }
    }

    /**
     * Sync interests.
     */
    private function syncInterests(User $user, array $interests)
    {
        $user->interests()->delete();

        foreach ($interests as $interest) {
            $user->interests()->create([
                'interest_name' => $interest,
                'category' => 'career',
            ]);
        }
    }

    /**
     * Sync projects.
     */
    private function syncProjects(User $user, array $projectTitles)
    {
        $user->projects()->delete();

        foreach ($projectTitles as $title) {
            $user->projects()->create([
                'title' => $title,
                'description' => '',
            ]);
        }
    }

    /**
     * Sync certifications.
     */
    private function syncCertifications(User $user, array $certNames)
    {
        $user->certifications()->delete();

        foreach ($certNames as $name) {
            $user->certifications()->create([
                'certification_name' => $name,
                'issuing_organization' => 'Not specified',
                'issue_date' => now(),
            ]);
        }
    }
}