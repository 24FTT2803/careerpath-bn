<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'student_id', 'programme',
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
    // RELATIONSHIPS - ADD ALL OF THESE
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
        $completed = 0;
        $total = 0;

        $sections = [
            'profile' => $this->profile && $this->profile->profile_complete,
            'academic' => $this->academicRecords()->exists(),
            'competencies' => $this->competencies()->exists(),
            'interests' => $this->interests()->exists(),
            'projects' => $this->projects()->exists(),
            'certifications' => $this->certifications()->exists(),
            'aspirations' => $this->aspirations()->exists(),
        ];

        foreach ($sections as $completed_flag) {
            $total++;
            if ($completed_flag) {
                $completed++;
            }
        }

        return $total > 0 ? round(($completed / $total) * 100) : 0;
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