<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',          // ← ADD THIS
        'title',
        'description',
        'technologies_used',
        'team_members',
        'role',
        'project_url',
        'start_date',
        'end_date',
        'achievements',
    ];

    protected $casts = [
        'technologies_used' => 'array',
        'team_members' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}