<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BiicfCompetency extends Model
{
    protected $table = 'biicf_competencies';

    protected $fillable = ['name', 'slug', 'type', 'description'];

    public function jobRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_job_role_competency',
            'competency_id',
            'job_role_id'
        )->withPivot('proficiency_level_id', 'is_core');
    }
}