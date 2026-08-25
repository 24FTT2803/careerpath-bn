<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'cgpa', 'role', 'avatar', 'last_login_at'
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
        $domain = substr(strrchr($email, "@"), 1);
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
        $pattern = '/^[a-zA-Z0-9._%+-]+@(' . implode('|', array_map('preg_quote', $allowedDomains)) . ')$/';
        
        return [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            'regex:' . $pattern,
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

    public function careerRecommendations()
    {
        return $this->hasMany(CareerRecommendation::class);
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