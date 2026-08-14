<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCompetency extends Model
{
    protected $fillable = [
        'user_id',          // ← ADD THIS
        'skill_name',
        'category',
        'proficiency_level',
        'evidence',
        'description',
    ];

    protected $casts = [
        'evidence' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}