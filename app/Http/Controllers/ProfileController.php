<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\AcademicRecord;
use App\Models\StudentCompetency;
use App\Models\StudentInterest;
use App\Models\StudentProject;
use App\Models\StudentCertification;
use App\Models\StudentAspiration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the student's profile.
     */
    public function index()
    {
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
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+\d\s\-\(\)]{7,20}$/'],
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
                'career_goals' => $request->filled('career_goals_text') ? [$request->career_goals_text] : [],
                'vision_statement' => $request->vision_statement,
                'long_term_goals' => $request->long_term_goals,
            ]
        );

        // Update profile completion
        $completionPercentage = $user->profile_completion;
        $user->profile()->update([
            'completion_percentage' => $completionPercentage,
            'profile_complete' => $completionPercentage >= 70,
        ]);

        return redirect()->route('student.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ============================================
    // SYNC METHODS
    // ============================================

    private function syncSkills($user, $skills)
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

    private function syncInterests($user, $interests)
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
private function syncProjects(User $user, array $projects)
{
    $existingIds = [];
    
    foreach ($projects as $projectData) {
        // Skip empty projects (no title)
        if (empty($projectData['title'])) {
            continue;
        }
        
        // Process technologies from comma-separated string to array
        $technologies = [];
        if (!empty($projectData['technologies_used'])) {
            $technologies = array_filter(array_map('trim', explode(',', $projectData['technologies_used'])));
        }
        
        // If project has an ID, update existing
        if (isset($projectData['id']) && !empty($projectData['id'])) {
            $project = StudentProject::where('user_id', $user->id)
                ->find($projectData['id']);
            
            if ($project) {
                $project->update([
                    'title' => $projectData['title'],
                    'description' => $projectData['description'] ?? '',
                    'technologies_used' => $technologies,
                    'role' => $projectData['role'] ?? '',
                    'project_url' => $projectData['project_url'] ?? '',
                    'start_date' => $projectData['start_date'] ?? null,
                    'end_date' => $projectData['end_date'] ?? null,
                    'achievements' => $projectData['achievements'] ?? '',
                ]);
                $existingIds[] = $project->id;
                continue;
            }
        }
        
        // Create new project
        $project = StudentProject::create([
            'user_id' => $user->id,
            'title' => $projectData['title'],
            'description' => $projectData['description'] ?? '',
            'technologies_used' => $technologies,
            'role' => $projectData['role'] ?? '',
            'project_url' => $projectData['project_url'] ?? '',
            'start_date' => $projectData['start_date'] ?? null,
            'end_date' => $projectData['end_date'] ?? null,
            'achievements' => $projectData['achievements'] ?? '',
        ]);
        $existingIds[] = $project->id;
    }
    
    // Delete removed projects
    if (!empty($existingIds)) {
        StudentProject::where('user_id', $user->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    } else {
        // If no projects were submitted, delete all
        $user->projects()->delete();
    }
}

    private function syncCertifications($user, $certNames)
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

    /**
 * Show settings page.
 */
public function settings()
{
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

    $user = Auth::user();
    $user->update([
        'password' => bcrypt($request->password),
    ]);

    return redirect()->route('student.settings')
        ->with('success', 'Password updated successfully!');
}

use App\Models\Notification;

/**
 * Show notifications page.
 */
public function notifications()
{
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
}

