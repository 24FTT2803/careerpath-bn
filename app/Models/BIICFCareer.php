<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BIICFCareer extends Model
{
    protected $table = 'biicf_careers';

    protected $fillable = [
        'job_title',
        'subsector',
        'technical_skills',
        'soft_skills',
        'entry_requirements',
        'recommended_training',
        'certifications',
        'job_description',
        'demand_level',
    ];

    protected $casts = [
        'technical_skills' => 'array',
        'soft_skills' => 'array',
        'entry_requirements' => 'array',
        'recommended_training' => 'array',
        'certifications' => 'array',
    ];

    public function recommendations()
    {
        return $this->hasMany(
            CareerRecommendation::class,
            'biicf_career_id'
        );
    }
}