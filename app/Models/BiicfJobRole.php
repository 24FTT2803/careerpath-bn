<?php

namespace App\Models;

use App\Models\Concerns\HasEditionProvenance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BiicfJobRole extends Model
{
    use HasEditionProvenance;

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

    /**
     * The columns of this role's competency table.
     *
     * Either numbered steps within one job level or the job
     * levels the role spans — see BiicfJobRoleTier. Every role
     * has at least one.
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(
            BiicfJobRoleTier::class,
            'job_role_id'
        )->orderBy('sort_order');
    }

    /**
     * The framework's named job levels for this role. A role can
     * sit at more than one.
     */
    public function jobLevels(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobLevel::class,
            'biicf_job_role_job_level',
            'job_role_id',
            'job_level_id'
        )->orderBy('sort_order');
    }

    public function jobFamilies(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobFamily::class,
            'biicf_job_role_job_family',
            'job_role_id',
            'job_family_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
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
