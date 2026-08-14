<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAspiration extends Model
{
    protected $fillable = [
        'user_id',          // ← ADD THIS
        'career_goals',
        'preferred_industries',
        'preferred_work_activities',
        'vision_statement',
        'mission_statement',
        'long_term_goals',
    ];

    protected $casts = [
        'career_goals' => 'array',
        'preferred_industries' => 'array',
        'preferred_work_activities' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}