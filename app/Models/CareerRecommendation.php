<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'recommendation_generation_id',
        'biicf_career_id',
        'biicf_job_role_id',
        'rank',
        'match_score',
        'matched_skills',
        'skill_gaps',
        'development_plan',
        'career_readiness_score',
        'explanation',
    ];

    protected $casts = [
        'rank' => 'integer',
        'match_score' => 'float',
        'career_readiness_score' => 'float',
        'matched_skills' => 'array',
        'skill_gaps' => 'array',
        'development_plan' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The historical generation this recommendation belongs to.
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(
            RecommendationGeneration::class,
            'recommendation_generation_id'
        );
    }

    /**
     * Legacy five-role CareerPath relationship.
     */
    public function career()
    {
        return $this->belongsTo(
            BIICFCareer::class,
            'biicf_career_id'
        );
    }

    /**
     * Current BIICF job-role relationship.
     */
    public function jobRole()
    {
        return $this->belongsTo(
            BiicfJobRole::class,
            'biicf_job_role_id'
        );
    }
}
