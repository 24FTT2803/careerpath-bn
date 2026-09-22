<?php

namespace App\Http\Controllers\Student;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\BiicfCompetency;
use App\Models\BiicfProficiencyLevel;
use App\Models\Notification;
use App\Models\Plan;
use App\Models\RecommendationGeneration;
use App\Models\StudentCompetency;
use App\Models\StudentInterest;
use App\Models\StudentMilestone;
use App\Models\StudentProject;
use App\Models\User;
use App\Services\AI\CareerReportBuilder;
use App\Services\AI\RecommendationStatusService;
use App\Services\Business\EntitlementService;
use App\Services\Business\ProgrammeEnrolmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;

class ProfileController extends Controller
{
    private const PREDEFINED_SKILLS = [
        'Python',
        'JavaScript',
        'SQL',
        'Java',
        'PHP',
        'HTML/CSS',
        'React',
        'Node.js',
        'Git',
        'Linux',
        'Docker',
        'AWS',
        'C++',
        'C#',
        'Ruby',
    ];

    private const SKILL_ALIAS_GROUPS = [
        ['JavaScript', 'JS'],
        ['TypeScript', 'TS'],
        ['Python', 'Py'],
        ['Kubernetes', 'K8s'],
        ['Amazon Web Services', 'AWS'],
        ['Node.js', 'NodeJS', 'Node JS'],
        ['HTML/CSS', 'HTML CSS'],
        ['C++', 'CPP', 'C Plus Plus'],
        ['C#', 'C Sharp'],
    ];

    private const PREDEFINED_INTERESTS = [
        'Problem Solving',
        'Teamwork',
        'Communication',
        'Leadership',
        'Creativity',
        'Analytical Thinking',
        'Research',
        'Writing',
        'Public Speaking',
        'Programming',
        'Data Analysis',
        'Networking',
        'Cybersecurity',
        'Cloud Computing',
        'Project Management',
    ];

    /**
     * Small, intentionally conservative groups of equivalent ICT terms.
     * These are used for duplicate prevention, not as a general dictionary.
     */
    private const INTEREST_ALIAS_GROUPS = [
        ['Artificial Intelligence', 'AI'],
        ['Machine Learning', 'ML'],
        ['UI/UX', 'UI UX', 'UIUX'],
        ['Cybersecurity', 'Cyber Security'],
        ['Application Development', 'App Development', 'App Dev'],
        ['Web Development', 'Web Dev'],
        ['Database', 'DB'],
    ];

    /**
     * Only unmistakable keyboard-smash values are blocked outright.
     * Unfamiliar or emerging terms are handled as warnings in the UI.
     */
    private const CLEAR_NONSENSE_INTERESTS = [
        'qwerty',
        'qwertyui',
        'qwertyuiop',
        'asdfgh',
        'asdfghjkl',
        'zxcvbn',
        'zxcvbnm',
    ];

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
            'aspirations',
        ]);

        $profileCompletion = $user->profile_completion;

        $skillOptions = BiicfCompetency::query()
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $interestOptions = self::PREDEFINED_INTERESTS;

        return view(
            'student.profile.index',
            compact(
                'user',
                'profileCompletion',
                'skillOptions',
                'interestOptions'
            )
        );
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
            'aspirations',
        ]);

        $profileCompletion = $user->profile_completion;
        $skillAliasGroups = self::SKILL_ALIAS_GROUPS;

        $biicfCompetencies = BiicfCompetency::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $proficiencyLevels = BiicfProficiencyLevel::query()
            ->orderBy('level_number')
            ->get();

        $skillOptions = $biicfCompetencies
            ->pluck('name')
            ->all();

        $interestOptions = self::PREDEFINED_INTERESTS;
        $interestAliasGroups = self::INTEREST_ALIAS_GROUPS;

        return view(
            'student.profile.edit',
            compact(
                'user',
                'profileCompletion',
                'skillOptions',
                'skillAliasGroups',
                'biicfCompetencies',
                'proficiencyLevels',
                'interestOptions',
                'interestAliasGroups'
            )
        );
    }

    /**
     * Update the user's profile information.
     */
    public function update(
        Request $request,
        RecommendationStatusService $recommendationStatus
    ) {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'student_id' => ['nullable', 'string', 'max:20', 'unique:users,student_id,'.$user->id],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                (new Phone)->countryField(
                    'phone_country'
                ),
            ],

            'phone_country' => [
                'nullable',
                'required_with:phone',
                'string',
                'size:2',
            ],
            'address' => ['nullable', 'string', 'max:300'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'profile_picture' => [
                'nullable',
                File::image()
                    ->types([
                        'jpg',
                        'jpeg',
                        'png',
                        'webp',
                    ])
                    ->max('5mb'),
            ],

            'remove_profile_picture' => [
                'nullable',
                'boolean',
            ],
            'programme' => [
                'nullable',
                'string',
                Rule::exists('organisation_groups', 'name'),
            ],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],

            'skills' => ['nullable', 'array'],
            'skills.*' => [
                'string',
                Rule::in(self::PREDEFINED_SKILLS),
            ],

            'biicf_mode' => [
                'nullable',
                'boolean',
            ],

            'biicf_competencies' => [
                'nullable',
                'array',
            ],

            'biicf_competencies.*' => [
                'nullable',
                'array',
            ],

            'biicf_competencies.*.selected' => [
                'nullable',
                'boolean',
            ],

            'biicf_competencies.*.proficiency_level_id' => [
                'nullable',
                'integer',
                'exists:biicf_proficiency_levels,id',
            ],

            'custom_skills' => ['nullable', 'array'],
            'custom_skills.*' => [
                'nullable',
                'string',
                'max:40',
            ],

            'custom_skill_levels' => [
                'nullable',
                'array',
            ],

            'custom_skill_levels.*' => [
                'nullable',
                'integer',
                'exists:biicf_proficiency_levels,id',
            ],

            'interests' => ['nullable', 'array'],
            'interests.*' => [
                'string',
                Rule::in(self::PREDEFINED_INTERESTS),
            ],

            'custom_interests' => ['nullable', 'array'],
            'custom_interests.*' => [
                'nullable',
                'string',
                'max:40',
            ],

            'projects' => ['nullable', 'array'],

            'projects.*.id' => [
                'nullable',
                'integer',
                'distinct',
            ],

            'projects.*.title' => [
                'required',
                'string',
                'max:150',
            ],

            'projects.*.role' => [
                'nullable',
                'string',
                'max:100',
            ],

            'projects.*.project_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'projects.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'projects.*.technologies_used' => [
                'nullable',
                'string',
                'max:500',
            ],

            'projects.*.start_date' => [
                'nullable',
                'date',
            ],

            'projects.*.end_date' => [
                'nullable',
                'date',
            ],

            'projects.*.achievements' => [
                'nullable',
                'string',
                'max:500',
            ],

            'certifications' => ['nullable', 'array'],

            'certifications.*.id' => [
                'nullable',
                'integer',
                'distinct',
            ],

            'certifications.*.certification_name' => [
                'required',
                'string',
                'max:150',
            ],

            'certifications.*.issuing_organization' => [
                'nullable',
                'string',
                'max:150',
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
                    'png',
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

            'career_goals_text' => ['nullable', 'string', 'max:100'],
            'vision_statement' => ['nullable', 'string', 'max:500'],
            'long_term_goals' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.phone' => 'Enter a valid phone number for the selected country.',

            'phone.max' => 'The phone number is too long.',

            'phone_country.required_with' => 'Please select a country for the phone number.',

            'phone_country.size' => 'The selected phone country is invalid.',
            'student_id.unique' => 'This Student ID is already taken.',
            'cgpa.min' => 'CGPA must be at least 0.',
            'cgpa.max' => 'CGPA cannot exceed 4.0.',
        ]);

        /*
        * Prepare skills before any profile data is written.
        *
        * This ensures an invalid competency selection, such as
        * a selected competency without a proficiency level,
        * returns the user to the form before anything is saved.
        */
        if ($request->boolean('biicf_mode')) {
            $skills = $this->prepareBiicfSkills(
                $request->input(
                    'biicf_competencies',
                    []
                ),
                $request->input(
                    'custom_skills',
                    []
                ),
                $request->input(
                    'custom_skill_levels',
                    []
                )
            );
        } else {
            $skills = $this->prepareSkills(
                $request->input(
                    'skills',
                    []
                ),
                $request->input(
                    'custom_skills',
                    []
                )
            );
        }

        $normalizedPhone =
            null;

        if (
            $request->filled('phone')
        ) {
            $normalizedPhone =
                (new PhoneNumber(
                    $request->phone,
                    strtoupper(
                        $request->phone_country
                    )
                ))
                    ->formatE164();

            $phoneAlreadyExists =
                User::query()
                    ->where(
                        'phone',
                        $normalizedPhone
                    )
                    ->where(
                        'id',
                        '!=',
                        $user->id
                    )
                    ->exists();

            if ($phoneAlreadyExists) {
                throw ValidationException::withMessages([
                    'phone' => 'This phone number is already registered to another account.',
                ]);
            }
        }

        // Process profile picture
        $profilePicturePath =
            $user->profile?->profile_picture;

        if (
            $request->hasFile(
                'profile_picture'
            )
        ) {
            $newProfilePicturePath =
                $request
                    ->file('profile_picture')
                    ->store(
                        'profile-pictures',
                        'public'
                    );

            if (
                $profilePicturePath
                && Storage::disk('public')
                    ->exists(
                        $profilePicturePath
                    )
            ) {
                Storage::disk('public')
                    ->delete(
                        $profilePicturePath
                    );
            }

            $profilePicturePath =
                $newProfilePicturePath;
        } elseif (
            $request->boolean(
                'remove_profile_picture'
            )
        ) {
            if (
                $profilePicturePath
                && Storage::disk('public')
                    ->exists(
                        $profilePicturePath
                    )
            ) {
                Storage::disk('public')
                    ->delete(
                        $profilePicturePath
                    );
            }

            $profilePicturePath =
                null;
        }

        // Combine first and last name into full name
        $fullName = $request->first_name.' '.$request->last_name;

        // Update User
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $fullName,
            'student_id' => $request->student_id,
            'programme' => $request->programme,
            'cgpa' => $request->cgpa,
            'phone' => $normalizedPhone,
        ]);

        // Update Profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $normalizedPhone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'nationality' => $request->nationality,
                'profile_picture' => $profilePicturePath,
                'bio' => $request->bio,
            ]
        );

        // Sync Skills
        $this->syncSkills(
            $user,
            $skills
        );

        // Process Interests
        $interests = $this->prepareInterests(
            $request->input('interests', []),
            $request->input('custom_interests', [])
        );

        $this->syncInterests(
            $user,
            $interests
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

        // ============================================
        // LOG ACTIVITY - Profile updated
        // ============================================
        NotificationHelper::logProfileUpdate($user->id, $user->name);

        /*
         * Recommendations are never regenerated automatically.
         * Changing AI-relevant profile data only marks existing
         * generations outdated, leaving the student in control
         * of when to generate again.
         */
        $recommendationStatus->refreshFor($user->fresh());

        /*
         * Keep the student's place in the structure matching the
         * programme they chose, so sponsorship and advertising
         * aimed at their school or programme can find them.
         */
        app(ProgrammeEnrolmentService::class)->syncFor(
            $user->fresh(),
            $user->programme
        );

        $response = redirect()
            ->route('student.profile')
            ->with(
                'success',
                'Profile updated successfully!'
            );

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

        $profilePicturePath =
            $user->profile?->profile_picture;

        if (
            $profilePicturePath
            && Storage::disk('public')
                ->exists(
                    $profilePicturePath
                )
        ) {
            Storage::disk('public')
                ->delete(
                    $profilePicturePath
                );
        }

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

    /**
     * Export student profile as PDF (for students)
     */
    public function export(CareerReportBuilder $reportBuilder)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isStudent()) {
            abort(403, 'Only students can export profiles.');
        }

        $pdf = Pdf::loadView(
            'student.profile.export',
            $reportBuilder->for($user)
        )->setPaper('a4', 'portrait');

        $filename = 'CareerPath-BN-'.Str::slug($user->name).'-Profile.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export student profile as PDF (for admin/lecturer)
     */
    public function exportAdmin(
        $userId,
        CareerReportBuilder $reportBuilder,
        ?RecommendationGeneration $generation = null
    ) {
                $user = User::findOrFail($userId);
        $currentUser = Auth::user();

        // Only allow admins and lecturers to view other students' profiles
        if (
            $currentUser->id !== $user->id
            && ! in_array(
                $currentUser->role,
                ['admin', 'lecturer']
            )
        ) {
            abort(403, 'Unauthorized access.');
        }

        /*
         * A lecturer may only export students in the classes
         * they teach. 404 rather than 403 so the lecturer
         * cannot confirm the student exists outside their
         * scope.
         */
        if (
            $currentUser->role === 'lecturer'
            && $currentUser->id !== $user->id
        ) {
            $scope = app(
                \App\Services\Lecturer\LecturerScopeResolver::class
            );

            abort_unless(
                $user->role === 'student'
                    && $scope->canSeeStudent($currentUser, $user),
                404
            );
        }

        if (! $user->isStudent()) {
            abort(403, 'Only students can export profiles.');
        }

        /*
         * Staff reviewing a student are not subject to the
         * student's own download entitlement. That flag is a
         * commercial gate on students, not a restriction on the
         * institution.
         */
        if (
            $generation !== null
            && $generation->user_id !== $user->id
        ) {
            abort(404);
        }

        $pdf = Pdf::loadView(
            'student.profile.export',
            $reportBuilder->for($user, $generation)
        )->setPaper('a4', 'portrait');

        $filename = 'CareerPath-BN-'.Str::slug($user->name).'-Profile.pdf';

        return $pdf->download($filename);
    }

    // ============================================
    // SETTINGS METHODS
    // ============================================

    /**
     * Show settings page.
     */
    public function settings(EntitlementService $entitlements)
    {
        /** @var User $user */
        $user = Auth::user();

        return view(
            'student.settings.index',
            [
                'user' => $user,
                'plan' => $entitlements->planFor($user),
                'adsAvailable' => $entitlements->adsAvailable($user),
                'canChooseAds' => $entitlements->canChooseAds($user),
                'showsAds' => $entitlements->shouldShowAds($user),
            ]
        );
    }

    /**
     * Move a student onto Premium.
     *
     * No payment is taken. This stands in for a payment step
     * that is outside the project's scope, and the student is
     * told so plainly rather than being led to believe they
     * have bought something.
     */
    public function upgrade(EntitlementService $entitlements)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->isStudent(),
            403
        );

        $premium = Plan::where('code', 'premium')
            ->where('is_active', true)
            ->first();

        if ($premium === null) {
            return redirect()
                ->route('student.settings')
                ->withErrors([
                    'upgrade' => 'Premium is not available at the moment.',
                ]);
        }

        if ($entitlements->planFor($user)?->code === 'premium') {
            return redirect()
                ->route('student.settings')
                ->with('success', 'You are already on Premium.');
        }

        /*
         * Any earlier grant is closed rather than left running,
         * so one account never holds two live grants.
         */
        $user->planGrants()
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'ends_at' => now(),
            ]);

        $user->planGrants()->create([
            'plan_id' => $premium->id,
            'source' => 'self',
            'is_active' => true,
        ]);

        /*
         * Premium makes advertising optional, and the default
         * for a paid plan is off.
         */
        $user->update(['show_ads' => false]);

        return redirect()
            ->route('student.settings')
            ->with(
                'success',
                'You are now on Premium. No payment was taken — '
                .'this is a demonstration of the upgrade, not a purchase.'
            );
    }

    /**
     * Update the student's advertising preference.
     *
     * Only meaningful on a plan that does not carry advertising.
     * On the free plan advertising is what pays for free access,
     * so it is not the student's to switch off.
     */
    public function updatePreferences(
        Request $request,
        EntitlementService $entitlements
    ) {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->isStudent(),
            403
        );

        if (! $entitlements->canChooseAds($user)) {
            return redirect()
                ->route('student.settings')
                ->withErrors([
                    'show_ads' => 'Advertising is part of your current plan.',
                ]);
        }

        $user->update([
            'show_ads' => $request->boolean('show_ads'),
        ]);

        return redirect()
            ->route('student.settings')
            ->with(
                'success',
                'Your preferences have been saved.'
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
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
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
     * Return recent notifications for the popup.
     *
     * Capped deliberately. The popup is a preview, and the
     * full list lives on the notifications page.
     */
    public function recentNotifications()
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function (Notification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'is_read' => (bool) $notification->is_read,
                    'created_at_human' => $notification
                        ->created_at
                        ->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
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

        /*
         * The popup submits this in the background, so it
         * expects JSON. The notifications page still submits
         * normally and gets the redirect and flash message.
         */
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

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
                'max:255',
            ],

            'category' => [
                'required',
                'string',
                'in:academic,career,personal,skill',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'target_date' => [
                'nullable',
                'date',
                'after:today',
            ],
        ]);

        StudentMilestone::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'target_date' => $request->target_date,
        ]);

        // Log milestone added
        NotificationHelper::logMilestoneActivity(
            Auth::id(),
            $request->title,
            'added'
        );

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

        // Log milestone completed
        NotificationHelper::logMilestoneActivity(
            Auth::id(),
            $milestone->title,
            'completed'
        );

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

        // Log milestone deleted
        NotificationHelper::logMilestoneActivity(
            Auth::id(),
            $milestone->title,
            'deleted'
        );

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
            /*
            * BIICF-aligned skills are prepared as structured
            * arrays containing their real competency type and
            * proficiency level.
            *
            * Plain strings remain supported temporarily for
            * the legacy profile UI.
            */
            if (is_array($skill)) {
                $user
                    ->competencies()
                    ->create([
                        'skill_name' => $skill['skill_name'],

                        'category' => $skill['category'],

                        'proficiency_level' => $skill['proficiency_level'],
                    ]);

                continue;
            }

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
     * Prepare BIICF competencies and additional skills for storage.
     *
     * BIICF competencies use the authoritative name and type stored
     * in the BIICF database. StudentCompetency remains the storage
     * model so existing profile, admin, export and recommendation
     * features continue to work.
     */
    private function prepareBiicfSkills(
        array $competencySelections,
        array $customSkills,
        array $customSkillLevels
    ): array {
        $competencies =
            BiicfCompetency::query()
                ->get()
                ->keyBy(
                    fn ($competency) => (string) $competency->id
                );

        $proficiencyLevels =
            BiicfProficiencyLevel::query()
                ->get()
                ->keyBy(
                    fn ($level) => (string) $level->id
                );

        /*
        * Build a lookup of every official BIICF competency.
        * This prevents a student from entering an official
        * competency again as an additional skill.
        */
        $biicfKeys = [];

        foreach ($competencies as $competency) {
            $biicfKeys[
                $this->canonicalSkillKey(
                    $competency->name
                )
            ] = $competency->name;
        }

        $seenKeys = [];
        $prepared = [];

        /*
        * Prepare officially selected BIICF competencies.
        */
        foreach (
            $competencySelections as $competencyId => $selection
        ) {
            if (
                ! is_array($selection)
                || empty($selection['selected'])
            ) {
                continue;
            }

            $competency =
                $competencies->get(
                    (string) $competencyId
                );

            if (! $competency) {
                throw ValidationException::withMessages([
                    'biicf_competencies' => 'One of the selected BIICF competencies is invalid.',
                ]);
            }

            $levelId =
                $selection[
                    'proficiency_level_id'
                ] ?? null;

            $level =
                $proficiencyLevels->get(
                    (string) $levelId
                );

            if (! $level) {
                throw ValidationException::withMessages([
                    "biicf_competencies.$competencyId.proficiency_level_id" => 'Select a proficiency level for '
                        .$competency->name
                        .'.',
                ]);
            }

            $key =
                $this->canonicalSkillKey(
                    $competency->name
                );

            $seenKeys[$key] =
                $competency->name;

            $prepared[] = [
                'skill_name' => $competency->name,

                'category' => $competency->type,

                'proficiency_level' => $level->name,
            ];
        }

        /*
        * Prepare additional student-entered skills.
        *
        * Additional skills remain technical by default because
        * official BIICF soft-skill competencies are available
        * separately through the BIICF competency list.
        */
        foreach ($customSkills as $index => $skill) {
            $clean = preg_replace(
                '/\s+/u',
                ' ',
                trim((string) $skill)
            );

            $clean =
                $clean
                ?? trim((string) $skill);

            if ($clean === '') {
                continue;
            }

            if (mb_strlen($clean) > 40) {
                throw ValidationException::withMessages([
                    'custom_skills' => 'Each additional skill must be 40 characters or fewer.',
                ]);
            }

            if (
                $this->isClearlyInvalidSkill(
                    $clean
                )
            ) {
                throw ValidationException::withMessages([
                    'custom_skills' => '"'.$clean.'" does not look like a usable skill. '
                        .'Please enter a skill, technology, tool, method, or competency you have.',
                ]);
            }

            $key =
                $this->canonicalSkillKey(
                    $clean
                );

            if (isset($biicfKeys[$key])) {
                throw ValidationException::withMessages([
                    'custom_skills' => '"'.$clean
                        .'" matches the BIICF competency "'
                        .$biicfKeys[$key]
                        .'". Select that competency instead.',
                ]);
            }

            if (isset($seenKeys[$key])) {
                throw ValidationException::withMessages([
                    'custom_skills' => '"'.$clean
                        .'" duplicates "'
                        .$seenKeys[$key]
                        .'". Each skill should only be added once.',
                ]);
            }

            $levelId =
                $customSkillLevels[$index]
                ?? null;

            $level =
                $proficiencyLevels->get(
                    (string) $levelId
                );

            if (! $level) {
                throw ValidationException::withMessages([
                    "custom_skill_levels.$index" => 'Select a proficiency level for '
                        .$clean
                        .'.',
                ]);
            }

            $seenKeys[$key] =
                $clean;

            $prepared[] = [
                'skill_name' => $clean,

                'category' => 'technical',

                'proficiency_level' => $level->name,
            ];
        }

        return $prepared;
    }

    /**
     * Prepare predefined and additional skills for storage.
     *
     * Additional skills are stored as ordinary StudentCompetency records.
     * Whether a skill is additional is determined by comparing it with
     * PREDEFINED_SKILLS when the profile is displayed or edited.
     */
    private function prepareSkills(
        array $predefinedSkills,
        array $customSkills
    ): array {
        $selectedPredefined = array_values(
            array_unique(
                array_filter(
                    $predefinedSkills,
                    fn ($skill) => in_array(
                        $skill,
                        self::PREDEFINED_SKILLS,
                        true
                    )
                )
            )
        );

        $predefinedKeys = [];

        foreach (self::PREDEFINED_SKILLS as $skill) {
            $predefinedKeys[
                $this->canonicalSkillKey($skill)
            ] = $skill;
        }

        $seenKeys = [];

        foreach ($selectedPredefined as $skill) {
            $seenKeys[
                $this->canonicalSkillKey($skill)
            ] = $skill;
        }

        $prepared = $selectedPredefined;

        foreach ($customSkills as $skill) {
            $clean = preg_replace(
                '/\s+/u',
                ' ',
                trim((string) $skill)
            );

            $clean = $clean
                ?? trim((string) $skill);

            if ($clean === '') {
                continue;
            }

            if (mb_strlen($clean) > 40) {
                throw ValidationException::withMessages([
                    'custom_skills' => 'Each additional skill must be 40 characters or fewer.',
                ]);
            }

            if ($this->isClearlyInvalidSkill($clean)) {
                throw ValidationException::withMessages([
                    'custom_skills' => '"'.$clean.'" does not look like a usable skill. '
                        .'Please enter a skill, technology, tool, method, or competency you have.',
                ]);
            }

            $key = $this->canonicalSkillKey($clean);

            if (isset($predefinedKeys[$key])) {
                throw ValidationException::withMessages([
                    'custom_skills' => '"'.$clean.'" matches the predefined skill '
                        .'"'.$predefinedKeys[$key].'". Select that option instead.',
                ]);
            }

            if (isset($seenKeys[$key])) {
                throw ValidationException::withMessages([
                    'custom_skills' => '"'.$clean.'" duplicates '
                        .'"'.$seenKeys[$key].'". '
                        .'Each skill should only be added once.',
                ]);
            }

            $seenKeys[$key] = $clean;
            $prepared[] = $clean;
        }

        return $prepared;
    }

    /**
     * Convert a skill to a comparison key and apply known aliases.
     */
    private function canonicalSkillKey(string $skill): string
    {
        $key = $this->normaliseInterestKey($skill);

        foreach (self::SKILL_ALIAS_GROUPS as $group) {
            if (empty($group)) {
                continue;
            }

            foreach ($group as $alias) {
                if (
                    $key
                    === $this->normaliseInterestKey($alias)
                ) {
                    return $this->normaliseInterestKey(
                        $group[0]
                    );
                }
            }
        }

        return $key;
    }

    /**
     * Reject only unmistakable nonsense. Unfamiliar terms are warned about
     * in the UI rather than rejected so emerging technologies still work.
     */
    private function isClearlyInvalidSkill(string $skill): bool
    {
        $trimmed = trim($skill);

        if ($trimmed === '') {
            return true;
        }

        $lettersAndNumbers = preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '',
            $trimmed
        );

        if (
            ! is_string($lettersAndNumbers)
            || $lettersAndNumbers === ''
        ) {
            return true;
        }

        if (preg_match('/^\d+$/u', $lettersAndNumbers)) {
            return true;
        }

        $lower = mb_strtolower($lettersAndNumbers);

        if (
            mb_strlen($lower) >= 4
            && preg_match('/^(.)\1{3,}$/u', $lower)
        ) {
            return true;
        }

        return in_array(
            $lower,
            self::CLEAR_NONSENSE_INTERESTS,
            true
        );
    }

    /**
     * Prepare predefined and additional interests for storage.
     *
     * Additional interests are stored as ordinary StudentInterest records.
     * Whether an interest is additional is determined by comparing it with
     * PREDEFINED_INTERESTS when the profile is displayed or edited.
     */
    private function prepareInterests(
        array $predefinedInterests,
        array $customInterests
    ): array {
        $selectedPredefined = array_values(
            array_unique(
                array_filter(
                    $predefinedInterests,
                    fn ($interest) => in_array(
                        $interest,
                        self::PREDEFINED_INTERESTS,
                        true
                    )
                )
            )
        );

        $predefinedKeys = [];

        foreach (self::PREDEFINED_INTERESTS as $interest) {
            $predefinedKeys[
                $this->canonicalInterestKey($interest)
            ] = $interest;
        }

        $seenKeys = [];

        foreach ($selectedPredefined as $interest) {
            $seenKeys[
                $this->canonicalInterestKey($interest)
            ] = $interest;
        }

        $prepared = $selectedPredefined;

        foreach ($customInterests as $interest) {
            $clean = preg_replace(
                '/\s+/u',
                ' ',
                trim((string) $interest)
            );

            $clean = $clean
                ?? trim((string) $interest);

            if ($clean === '') {
                continue;
            }

            if (mb_strlen($clean) > 40) {
                throw ValidationException::withMessages([
                    'custom_interests' => 'Each additional interest must be 40 characters or fewer.',
                ]);
            }

            if ($this->isClearlyInvalidInterest($clean)) {
                throw ValidationException::withMessages([
                    'custom_interests' => '"'.$clean.'" does not look like a usable interest. '
                        .'Please enter a topic, field, technology, or activity you are interested in.',
                ]);
            }

            $key = $this->canonicalInterestKey($clean);

            if (isset($predefinedKeys[$key])) {
                throw ValidationException::withMessages([
                    'custom_interests' => '"'.$clean.'" matches the predefined interest '
                        .'"'.$predefinedKeys[$key].'". Select that option instead.',
                ]);
            }

            if (isset($seenKeys[$key])) {
                throw ValidationException::withMessages([
                    'custom_interests' => '"'.$clean.'" duplicates '
                        .'"'.$seenKeys[$key].'". '
                        .'Each interest should only be added once.',
                ]);
            }

            $seenKeys[$key] = $clean;
            $prepared[] = $clean;
        }

        return $prepared;
    }

    /**
     * Convert an interest to a comparison key and apply known aliases.
     */
    private function canonicalInterestKey(string $interest): string
    {
        $key = $this->normaliseInterestKey($interest);

        foreach (self::INTEREST_ALIAS_GROUPS as $group) {
            $canonical = $this->normaliseInterestKey(
                $group[0]
            );

            foreach ($group as $alias) {
                if (
                    $key
                    === $this->normaliseInterestKey($alias)
                ) {
                    return $canonical;
                }
            }
        }

        return $key;
    }

    /**
     * Normalise case and harmless separators for duplicate comparison.
     * Symbols such as +, # and . are intentionally preserved so values
     * such as C++, C# and .NET remain distinct.
     */
    private function normaliseInterestKey(string $interest): string
    {
        $normalised = Str::lower(
            trim($interest)
        );

        return preg_replace(
            '/[\s\/_-]+/u',
            '',
            $normalised
        ) ?? $normalised;
    }

    /**
     * Reject only high-confidence nonsense.
     * Unknown words, acronyms and emerging technology names are not blocked.
     */
    private function isClearlyInvalidInterest(string $interest): bool
    {
        $trimmed = trim($interest);

        if ($trimmed === '') {
            return true;
        }

        $lettersAndNumbers = preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '',
            $trimmed
        ) ?? '';

        if ($lettersAndNumbers === '') {
            return true;
        }

        if (preg_match('/^\d+$/u', $lettersAndNumbers)) {
            return true;
        }

        $lower = Str::lower(
            $lettersAndNumbers
        );

        if (
            mb_strlen($lower) >= 4
            && preg_match('/^(.)\1{3,}$/u', $lower)
        ) {
            return true;
        }

        return in_array(
            $lower,
            self::CLEAR_NONSENSE_INTERESTS,
            true
        );
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
                        'title' => $projectData['title']
                            ?? '',

                        'description' => $projectData['description']
                            ?? '',

                        'technologies_used' => $this->processCommaList(
                            $projectData[
                                'technologies_used'
                            ] ?? ''
                        ),

                        'role' => $projectData['role']
                            ?? '',

                        'project_url' => $projectData['project_url']
                            ?? '',

                        'start_date' => $projectData['start_date']
                            ?? null,

                        'end_date' => $projectData['end_date']
                            ?? null,

                        'achievements' => $projectData['achievements']
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

                'title' => $projectData['title'],

                'description' => $projectData['description']
                    ?? '',

                'technologies_used' => $this->processCommaList(
                    $projectData[
                        'technologies_used'
                    ] ?? ''
                ),

                'role' => $projectData['role']
                    ?? '',

                'project_url' => $projectData['project_url']
                    ?? '',

                'start_date' => $projectData['start_date']
                    ?? null,

                'end_date' => $projectData['end_date']
                    ?? null,

                'achievements' => $projectData['achievements']
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
            ) as $index => $data
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
                    'certification_name' => $data[
                            'certification_name'
                        ],

                    'issuing_organization' => $data[
                            'issuing_organization'
                        ] ?? null,

                    'issue_date' => $data[
                            'issue_date'
                        ] ?? null,

                    'certificate_file_path' => $newFilePath
                        ?? $oldFilePath,

                    'certificate_original_name' => $newFilePath
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
            $certificationsToDelete as $certification
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
