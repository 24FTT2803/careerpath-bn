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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_id' => [
                'nullable',
                'string',
                'max:50',
                'unique:users,student_id,' . $user->id
            ],
            'phone' => User::getPhoneValidationRules(),
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'programme' => ['nullable', 'string', 'max:255'],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'skills' => ['nullable', 'array'],
            'interests' => ['nullable', 'array'],
            'projects' => ['nullable', 'array'],

            'certifications' => ['nullable', 'array'],

            'certifications.*.id' => [
                'nullable',
                'integer',
                'distinct',
            ],

            'certifications.*.certification_name' => [
                'required',
                'string',
                'max:255',
            ],

            'certifications.*.issuing_organization' => [
                'nullable',
                'string',
                'max:255',
            ],

            'certifications.*.issue_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'certifications.*.certificate_file' => [
                'nullable',
                File::types([
                    'pdf',
                    'jpg',
                    'jpeg',
                    'png'
                ])->max('5mb'),
            ],

            'removed_certification_ids' => [
                'nullable',
                'array',
            ],

            'removed_certification_ids.*' => [
                'integer',
                'distinct',
            ],

            'career_goals_text' => ['nullable', 'string'],
            'vision_statement' => ['nullable', 'string', 'max:500'],
            'long_term_goals' => ['nullable', 'string', 'max:500'],
        ]);

        // Combine first and last name into full name
        $fullName = $request->first_name . ' ' . $request->last_name;

        // Update User
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $fullName,
            'student_id' => $request->student_id,
            'programme' => $request->programme,
            'cgpa' => $request->cgpa,
            'phone' => $request->phone,
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
        $this->syncSkills(
            $user,
            $request->input('skills', [])
        );

        // Process Interests
        $this->syncInterests(
            $user,
            $request->input('interests', [])
        );

        // Process Projects
        if (
            $request->has('projects')
            && is_array($request->projects)
        ) {
            $this->syncProjects(
                $user,
                $request->projects
            );
        } else {
            $user->projects()->delete();
        }

        // Process Certifications
        $this->syncCertifications(
            $user,
            $request
        );

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

        // Generate or refresh career recommendations only
        // when the profile is at or above the threshold.
        $recommendationWarning = null;

        if ($profileIsComplete) {
            $user->refresh();

            try {
                $this->recommendationService->generateFor($user);
            } catch (\Throwable $exception) {
                report($exception);

                $recommendationWarning = $user
                    ->careerRecommendations()
                    ->exists()
                    ? 'Your profile was updated, but career recommendations could not be refreshed. Your previous recommendations are still available.'
                    : 'Your profile was updated, but career recommendations could not be generated. Please try again later.';
            }
        }

        $response = redirect()
            ->route('student.profile')
            ->with(
                'success',
                'Profile updated successfully!'
            );

        if ($recommendationWarning) {
            $response->with(
                'warning',
                $recommendationWarning
            );
        }

        return $response;
    }

    /**
     * Display a student's uploaded certification evidence.
     */
    public function certificationEvidence($certification)
    {
        /** @var User $user */
        $user = Auth::user();

        $certification = $user
            ->certifications()
            ->whereKey($certification)
            ->firstOrFail();

        $filePath = $certification
            ->certificate_file_path;

        abort_unless(
            $filePath
            && Storage::disk('local')->exists($filePath),
            404
        );

        $fileName = $certification
            ->certificate_original_name
            ?: basename($filePath);

        return Storage::disk('local')->response(
            $filePath,
            $fileName,
            [],
            'inline'
        );
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

        $request
            ->session()
            ->invalidate();

        $request
            ->session()
            ->regenerateToken();

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

        return view(
            'student.settings.index',
            compact('user')
        );
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required',
                'current_password'
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make(
                $request->password
            ),
        ]);

        return redirect()
            ->route('student.settings')
            ->with(
                'success',
                'Password updated successfully!'
            );
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

        $notifications = $user
            ->notifications()
            ->orderBy(
                'created_at',
                'desc'
            )
            ->paginate(20);

        $unreadCount = $user
            ->unreadNotifications()
            ->count();

        return view(
            'student.notifications.index',
            compact(
                'notifications',
                'unreadCount'
            )
        );
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::where(
            'user_id',
            Auth::id()
        )->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Notification marked as read.'
            );
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where(
            'user_id',
            Auth::id()
        )
            ->where(
                'is_read',
                false
            )
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'All notifications marked as read.'
            );
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

        $milestones = $user
            ->milestones()
            ->orderBy('is_completed')
            ->orderBy('target_date')
            ->get();

        return view(
            'student.milestones.index',
            compact('milestones')
        );
    }

    /**
     * Store a new milestone.
     */
    public function storeMilestone(Request $request)
    {
        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255'
            ],

            'category' => [
                'required',
                'string',
                'in:academic,career,personal,skill'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'target_date' => [
                'nullable',
                'date',
                'after:today'
            ],
        ]);

        StudentMilestone::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'target_date' => $request->target_date,
        ]);

        return redirect()
            ->route('student.milestones')
            ->with(
                'success',
                'Milestone added successfully!'
            );
    }

    /**
     * Complete a milestone.
     */
    public function completeMilestone($id)
    {
        $milestone = StudentMilestone::where(
            'user_id',
            Auth::id()
        )->findOrFail($id);

        $milestone->update([
            'is_completed' => true,
            'completed_date' => now(),
        ]);

        return redirect()
            ->route('student.milestones')
            ->with(
                'success',
                '🎉 Milestone completed!'
            );
    }

    /**
     * Delete a milestone.
     */
    public function destroyMilestone($id)
    {
        $milestone = StudentMilestone::where(
            'user_id',
            Auth::id()
        )->findOrFail($id);

        $milestone->delete();

        return redirect()
            ->route('student.milestones')
            ->with(
                'success',
                'Milestone deleted.'
            );
    }

    // ============================================
    // SYNC METHODS
    // ============================================

    /**
     * Sync skills/competencies.
     */
    private function syncSkills(
        User $user,
        array $skills
    ) {
        $user
            ->competencies()
            ->delete();

        foreach ($skills as $skill) {
            $user
                ->competencies()
                ->create([
                    'skill_name' => $skill,
                    'category' => 'technical',
                    'proficiency_level' => 'intermediate',
                ]);
        }
    }

    /**
     * Sync interests.
     */
    private function syncInterests(
        User $user,
        array $interests
    ) {
        $user
            ->interests()
            ->delete();

        foreach ($interests as $interest) {
            $user
                ->interests()
                ->create([
                    'interest_name' => $interest,
                    'category' => 'career',
                ]);
        }
    }

    /**
     * Sync projects.
     */
    private function syncProjects(
        User $user,
        array $projects
    ) {
        $existingIds = [];

        foreach ($projects as $projectData) {
            // If project has an ID, update existing.
            if (
                isset($projectData['id'])
                && ! empty($projectData['id'])
            ) {
                $project = StudentProject::where(
                    'user_id',
                    $user->id
                )->find(
                    $projectData['id']
                );

                if ($project) {
                    $project->update([
                        'title' =>
                            $projectData['title']
                            ?? '',

                        'description' =>
                            $projectData['description']
                            ?? '',

                        'technologies_used' =>
                            $this->processCommaList(
                                $projectData[
                                    'technologies_used'
                                ] ?? ''
                            ),

                        'role' =>
                            $projectData['role']
                            ?? '',

                        'project_url' =>
                            $projectData['project_url']
                            ?? '',

                        'start_date' =>
                            $projectData['start_date']
                            ?? null,

                        'end_date' =>
                            $projectData['end_date']
                            ?? null,

                        'achievements' =>
                            $projectData['achievements']
                            ?? '',
                    ]);

                    $existingIds[] = $project->id;

                    continue;
                }
            }

            // Create new project.
            // Skip completely empty project rows.
            if (empty($projectData['title'])) {
                continue;
            }

            $project = StudentProject::create([
                'user_id' => $user->id,

                'title' =>
                    $projectData['title'],

                'description' =>
                    $projectData['description']
                    ?? '',

                'technologies_used' =>
                    $this->processCommaList(
                        $projectData[
                            'technologies_used'
                        ] ?? ''
                    ),

                'role' =>
                    $projectData['role']
                    ?? '',

                'project_url' =>
                    $projectData['project_url']
                    ?? '',

                'start_date' =>
                    $projectData['start_date']
                    ?? null,

                'end_date' =>
                    $projectData['end_date']
                    ?? null,

                'achievements' =>
                    $projectData['achievements']
                    ?? '',
            ]);

            $existingIds[] = $project->id;
        }

        // Delete removed projects.
        if (! empty($existingIds)) {
            StudentProject::where(
                'user_id',
                $user->id
            )
                ->whereNotIn(
                    'id',
                    $existingIds
                )
                ->delete();
        } else {
            $user->projects()->delete();
        }
    }

    /**
     * Helper: Process comma-separated list to array.
     */
    private function processCommaList($input)
    {
        if (empty($input)) {
            return [];
        }

        return array_filter(
            array_map(
                'trim',
                explode(',', $input)
            )
        );
    }

    /**
     * Sync the student's certifications and uploaded evidence.
     */
    private function syncCertifications(
        User $user,
        Request $request
    ): void {
        foreach (
            $request->input(
                'certifications',
                []
            )
            as $index => $data
        ) {
            $certification = null;

            if (! empty($data['id'])) {
                $certification = $user
                    ->certifications()
                    ->whereKey(
                        $data['id']
                    )
                    ->firstOrFail();
            }

            $oldFilePath =
                $certification
                    ?->certificate_file_path;

            $oldOriginalName =
                $certification
                    ?->certificate_original_name;

            $newFilePath = null;
            $newOriginalName = null;

            try {
                if (
                    $request->hasFile(
                        "certifications.$index.certificate_file"
                    )
                ) {
                    $uploadedFile =
                        $request->file(
                            "certifications.$index.certificate_file"
                        );

                    $newOriginalName =
                        $uploadedFile
                            ->getClientOriginalName();

                    $newFilePath =
                        $uploadedFile
                            ->store(
                                "certifications/{$user->id}",
                                'local'
                            );

                    if (! $newFilePath) {
                        throw new \RuntimeException(
                            'The certificate evidence could not be stored.'
                        );
                    }
                }

                $values = [
                    'certification_name' =>
                        $data[
                            'certification_name'
                        ],

                    'issuing_organization' =>
                        $data[
                            'issuing_organization'
                        ] ?? null,

                    'issue_date' =>
                        $data[
                            'issue_date'
                        ] ?? null,

                    'certificate_file_path' =>
                        $newFilePath
                        ?? $oldFilePath,

                    'certificate_original_name' =>
                        $newFilePath
                            ? $newOriginalName
                            : $oldOriginalName,
                ];

                if ($certification) {
                    $certification
                        ->update(
                            $values
                        );
                } else {
                    $user
                        ->certifications()
                        ->create(
                            $values
                        );
                }
            } catch (\Throwable $exception) {
                /*
                 * If the new physical file was stored but
                 * the database update failed, remove only
                 * the new file. The previous evidence
                 * remains untouched.
                 */
                if ($newFilePath) {
                    Storage::disk('local')
                        ->delete(
                            $newFilePath
                        );
                }

                throw $exception;
            }

            /*
             * Only after the new evidence is safely stored
             * and recorded in the database do we remove
             * the previous evidence.
             */
            if (
                $newFilePath
                && $oldFilePath
                && $newFilePath !== $oldFilePath
            ) {
                Storage::disk('local')
                    ->delete(
                        $oldFilePath
                    );
            }
        }

        /*
         * Existing certifications are deleted only when
         * the browser explicitly submits their IDs for
         * removal.
         *
         * A certification simply missing from the normal
         * certifications array is not enough to authorise
         * deletion.
         */
        $removedIds = array_values(
            array_unique(
                array_map(
                    'intval',
                    $request->input(
                        'removed_certification_ids',
                        []
                    )
                )
            )
        );

        if (empty($removedIds)) {
            return;
        }

        $certificationsToDelete =
            $user
                ->certifications()
                ->whereIn(
                    'id',
                    $removedIds
                )
                ->get();

        foreach (
            $certificationsToDelete
            as $certification
        ) {
            $filePath =
                $certification
                    ->certificate_file_path;

            /*
             * Remove the database record first.
             *
             * If physical file deletion fails, an orphaned
             * private file is safer than a certification
             * record pointing at missing evidence.
             */
            $certification->delete();

            if ($filePath) {
                Storage::disk('local')
                    ->delete(
                        $filePath
                    );
            }
        }
    }
}