<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'biicf_career_id',
        'match_score',
        'matched_skills',
        'skill_gaps',
        'development_plan',
        'career_readiness_score',
    ];

    protected $casts = [
        'matched_skills' => 'array',
        'skill_gaps' => 'array',
        'development_plan' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function career()
    {
        return $this->belongsTo(BIICFCareer::class, 'biicf_career_id');
    }
}