<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentMilestone extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'category',
        'description',
        'target_date',
        'completed_date',
        'is_completed',
        'priority',
        'subtasks',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_date' => 'date',
        'is_completed' => 'boolean',
        'subtasks' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status of the milestone
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_completed) {
            return 'completed';
        }

        if (!$this->target_date) {
            return 'in_progress';
        }

        $targetEnd = Carbon::parse($this->target_date)->endOfDay();

        if ($targetEnd->lt(now())) {
            return 'past'; // Past milestone that wasn't completed
        }

        return 'in_progress';
    }

    /**
     * Check if milestone is overdue (only for future-dated incomplete milestones)
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->is_completed || !$this->target_date) {
            return false;
        }

        return Carbon::parse($this->target_date)->endOfDay()->lt(now());
    }

    /**
     * Check if milestone is in the past
     */
    public function getIsPastAttribute(): bool
    {
        if ($this->is_completed || !$this->target_date) {
            return false;
        }

        return Carbon::parse($this->target_date)->endOfDay()->lt(now());
    }
}