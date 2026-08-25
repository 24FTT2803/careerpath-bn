<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BiicfJobRole extends Model
{
    protected $table = 'biicf_job_roles';

    protected $fillable = [
        'sub_sector_id', 'title', 'slug', 'functional_group', 'job_description',
        'critical_work_function', 'alternative_titles', 'career_path_level', 'box_colour',
    ];

    protected $casts = [
        'alternative_titles' => 'array',
    ];

    public function subSector(): BelongsTo
    {
        return $this->belongsTo(BiicfSubSector::class, 'sub_sector_id');
    }

    // Roles this one can progress TO (career path forward)
    public function progressesTo(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_career_path_edges',
            'from_job_role_id',
            'to_job_role_id'
        )->withPivot('notes');
    }

    // Roles that progress INTO this one
    public function progressesFrom(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_career_path_edges',
            'to_job_role_id',
            'from_job_role_id'
        )->withPivot('notes');
    }

    public function competencies(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfCompetency::class,
            'biicf_job_role_competency',
            'job_role_id',
            'competency_id'
        )->withPivot('proficiency_level_id', 'is_core');
    }

    public function entryRequirement(): HasOne
    {
        return $this->hasOne(BiicfEntryRequirement::class, 'job_role_id');
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfTraining::class,
            'biicf_job_role_training',
            'job_role_id',
            'training_id'
        );
    }
}