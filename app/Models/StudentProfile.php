<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'date_of_birth',
        'nationality',
        'profile_picture',
        'social_links',
        'bio',
        'profile_complete',
        'completion_percentage',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'social_links' => 'array',
        'profile_complete' => 'boolean',
        'completion_percentage' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}