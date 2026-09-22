<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ============================================
    // EMAIL DOMAIN VALIDATION CONSTANTS
    // ============================================

    /**
     * Allowed email domains for registration
     */
    const ALLOWED_DOMAINS = [
        'gmail.com',
        'pb.edu.bn',
        'student.pb.edu.bn',
    ];

    /**
     * Email domain rules by role
     */
    const ROLE_DOMAIN_RULES = [
        'student' => ['gmail.com', 'student.pb.edu.bn', 'pb.edu.bn'],
        'lecturer' => ['gmail.com', 'pb.edu.bn'],
        'admin' => ['gmail.com', 'pb.edu.bn'],
    ];

    protected $fillable = [
        'first_name', 'last_name', 'name', 'email', 'phone', 'password', 'student_id', 'programme',
        'programme_group_id',
        'cgpa', 'role', 'avatar', 'show_ads', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'last_active_at' => 'datetime',
            'show_ads' => 'boolean',
        ];
    }

    // ============================================
    // VALIDATION HELPERS
    // ============================================

    /**
     * Get the allowed domains for a specific role
     */
    public static function getAllowedDomainsForRole(string $role): array
    {
        return self::ROLE_DOMAIN_RULES[$role] ?? self::ALLOWED_DOMAINS;
    }

    /**
     * Validate email domain for a specific role
     */
    public static function validateEmailDomain(string $email, string $role): bool
    {
        $domain = substr(strrchr($email, '@'), 1);
        $allowedDomains = self::getAllowedDomainsForRole($role);

        return in_array($domain, $allowedDomains);
    }

    /**
     * Get validation rules for email based on role
     */
    public static function getEmailValidationRules(string $role): array
    {
        $allowedDomains = self::getAllowedDomainsForRole($role);

        // Build regex pattern for allowed domains
        $pattern = '/^[a-zA-Z0-9._%+-]+@('.implode('|', array_map('preg_quote', $allowedDomains)).')$/';

        return [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            'regex:'.$pattern,
        ];
    }

    /**
     * Get phone number validation rules
     */
    public static function getPhoneValidationRules(): array
    {
        return [
            'nullable',
            'string',
            'max:20',
            'regex:/^[\+\d\s\-\(\)]{7,20}$/',
        ];
    }

    /**
     * Get custom validation messages for phone
     */
    public static function getPhoneValidationMessages(): array
    {
        return [
            'phone.regex' => 'The phone number format is invalid. Only digits, +, -, spaces, and parentheses are allowed.',
            'phone.max' => 'The phone number cannot exceed 20 characters.',
        ];
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the student profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Get the academic records for the user.
     */
    public function academicRecords()
    {
        return $this->hasMany(AcademicRecord::class);
    }

    /**
     * Get the competencies for the user.
     */
    public function competencies()
    {
        return $this->hasMany(StudentCompetency::class);
    }

    /**
     * Get the interests for the user.
     */
    public function interests()
    {
        return $this->hasMany(StudentInterest::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    /**
     * Get the projects for the user.
     */
    public function projects()
    {
        return $this->hasMany(StudentProject::class);
    }

    /**
     * Get the certifications for the user.
     */
    public function certifications()
    {
        return $this->hasMany(StudentCertification::class);
    }

    /**
     * Get the aspirations for the user.
     */
    public function aspirations()
    {
        return $this->hasOne(StudentAspiration::class);
    }

    /**
     * Get the learning records for the user.
     */
    public function learningRecords()
    {
        return $this->hasMany(StudentLearningRecord::class);
    }

    /**
     * Get the milestones for the user.
     */
    public function milestones()
    {
        return $this->hasMany(StudentMilestone::class);
    }

    /**
     * Every recommendation the user has ever been given, across
     * all generations.
     *
     * Screens showing the student's active results should use
     * currentRecommendations() instead, otherwise they render
     * the full history at once.
     */
    public function careerRecommendations()
    {
        return $this->hasMany(CareerRecommendation::class);
    }

    /**
     * Get the user's running Career Adviser thread.
     *
     * A student keeps one conversation rather than separate
     * threads, so this is a single record.
     */
    public function careerAdviserConversation(): HasOne
    {
        return $this->hasOne(
            CareerAdviserConversation::class
        );
    }

    /**
     * Get the user's recommendation generations.
     *
     * Intentionally unordered. The relation is also used for
     * aggregates and bulk status updates, and SQLite rejects
     * UPDATE statements carrying an ORDER BY clause.
     */
    public function recommendationGenerations(): HasMany
    {
        return $this->hasMany(
            RecommendationGeneration::class
        );
    }

    /**
     * Get the user's newest recommendation generation.
     *
     * Selected by generation number rather than by status. An
     * outdated generation is still the student's active set of
     * results, so keying this on status would empty every screen
     * the moment their profile changed.
     */
    public function currentRecommendationGeneration(): HasOne
    {
        return $this->hasOne(
            RecommendationGeneration::class
        )->ofMany(
            'generation_number',
            'max'
        );
    }

    /**
     * Get only the recommendations belonging to the user's
     * newest generation.
     *
     * The subquery resolves the newest generation per user, so
     * this stays correct when eager loaded across many users.
     */
    public function currentRecommendations(): HasMany
    {
        return $this->hasMany(
            CareerRecommendation::class
        )->whereIn(
            'recommendation_generation_id',
            fn ($query) => $query
                ->from('recommendation_generations')
                ->selectRaw('MAX(id)')
                ->groupBy('user_id')
        );
    }

    /**
     * Get the user's organisation group memberships.
     */
    /**
     * The programme group this student is enrolled in.
     *
     * users.programme holds the name for display and for the
     * record; this is what the enrolment actually rests on, so
     * renaming a programme does not detach anybody.
     */
    public function programmeGroup(): BelongsTo
    {
        return $this->belongsTo(
            OrganisationGroup::class,
            'programme_group_id'
        );
    }

    public function groupMemberships()
    {
        return $this->hasMany(GroupMembership::class);
    }

        /**
     * Classes this user teaches, when the user is a lecturer.
     *
     * Distinct from groupMemberships(), which is about
     * belonging. Teaching a class and being a member of it
     * are different things, and keeping them in separate
     * tables means the student-facing counts in
     * group_memberships stay honest.
     */
    public function lecturerAssignments(): HasMany
    {
        return $this->hasMany(
            LecturerAssignment::class
        );
    }

    /**
     * The organisation groups this lecturer is assigned to
     * teach, as a proper relation.
     */
    public function assignedGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganisationGroup::class,
            'lecturer_assignments',
            'user_id',
            'organisation_group_id'
        )->withTimestamps();
    }

    /**
     * Get the organisation groups the user belongs to.
     */
    public function organisationGroups()
    {
        return $this->belongsToMany(
            OrganisationGroup::class,
            'group_memberships'
        )->withTimestamps();
    }

    /**
     * Get direct plan grants assigned to the user.
     */
    public function planGrants()
    {
        return $this->hasMany(
            UserPlanGrant::class
        );
    }

    /**
     * Get feature usage records belonging to the user.
     */
    public function featureUsages()
    {
        return $this->hasMany(
            FeatureUsage::class
        );
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is a lecturer.
     */
    public function isLecturer()
    {
        return $this->role === 'lecturer';
    }

    /**
     * Calculate profile completion percentage.
     */
    public function getProfileCompletionAttribute()
    {
        $profile = $this->profile;
        $aspiration = $this->aspirations;

        $hasPersonalProfile = $profile && (
            filled($profile->phone)
            || filled($profile->address)
            || $profile->date_of_birth
            || filled($profile->nationality)
            || filled($profile->bio)
        );

        $hasAcademicInformation = (
            filled($this->programme)
            && $this->cgpa !== null
        );

        $hasMeaningfulAspirations = $aspiration && (
            ! empty($aspiration->career_goals)
            || ! empty($aspiration->preferred_industries)
            || ! empty($aspiration->preferred_work_activities)
            || filled($aspiration->vision_statement)
            || filled($aspiration->mission_statement)
            || filled($aspiration->long_term_goals)
        );

        $sections = [
            'profile' => $hasPersonalProfile,
            'academic' => $hasAcademicInformation,
            'competencies' => $this->competencies()->exists(),
            'interests' => $this->interests()->exists(),
            'projects' => $this->projects()->exists(),
            'certifications' => $this->certifications()->exists(),
            'aspirations' => $hasMeaningfulAspirations,
        ];

        $completed = count(
            array_filter($sections)
        );

        return round(
            ($completed / count($sections)) * 100
        );
    }

    /**
     * Whether this user is currently "online" - i.e. made a request
     * within the last 5 minutes. Backed by last_active_at, which is
     * stamped on every request by the TrackLastActive middleware.
     * Works identically in local dev and in production - it's just a
     * timestamp comparison, no special server/domain setup required.
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->last_active_at !== null
            && $this->last_active_at->gt(now()->subMinutes(5));
    }

    /**
     * Calculate career readiness score.
     */
    public function getReadinessScoreAttribute()
    {
        $score = 0;
        $count = 0;

        if ($this->cgpa) {
            $score += ($this->cgpa / 4.0) * 30;
            $count++;
        }

        if ($this->competencies()->exists()) {
            $score += min($this->competencies()->count() * 3, 30);
            $count++;
        }

        if ($this->certifications()->exists()) {
            $score += min($this->certifications()->count() * 7, 20);
            $count++;
        }

        if ($this->projects()->exists()) {
            $score += min($this->projects()->count() * 7, 20);
            $count++;
        }

        return $count > 0 ? round($score) : 0;
    }
}
