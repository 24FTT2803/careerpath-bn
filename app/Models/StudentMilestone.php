<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMilestone extends Model
{
    protected $fillable = [
        'user_id',          // ← ADD THIS
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
}