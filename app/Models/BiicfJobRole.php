<?php

namespace App\Models;

use App\Models\Concerns\HasEditionProvenance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

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

    /**
     * Every competency this role requires, across all its tiers.
     *
     * The proficiency on the pivot belongs to a tier, so a role
     * with more than one tier returns the same competency once
     * per tier, at each tier's own level. That is faithful to the
     * book's table but is rarely what a caller wants: for one
     * column of it, read a tier's own competencies, and for the
     * requirement to enter the role, read entryTier.
     */
    public function competencies(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfCompetency::class,
            'biicf_job_role_competency',
            'job_role_id',
            'competency_id'
        )->withPivot('proficiency_level_id', 'is_core', 'job_role_tier_id');
    }

    /**
     * The requirement for entering this role at all.
     *
     * Requirements now belong to tiers, and a tier can list
     * several alternatives, so this is the lowest tier's first
     * alternative — the least that will get someone in. Anything
     * showing the full picture should walk tiers and their
     * entryRequirements instead.
     */
    public function entryRequirement(): HasOneThrough
    {
        return $this->hasOneThrough(
            BiicfEntryRequirement::class,
            BiicfJobRoleTier::class,
            'job_role_id',
            'job_role_tier_id',
            'id',
            'id'
        )
            ->orderBy('biicf_job_role_tiers.sort_order')
            ->orderBy('biicf_entry_requirements.alternative_number');
    }

    /**
     * The role's description, as the book writes it: a short
     * list rather than a paragraph.
     */
    public function descriptionPoints(): HasMany
    {
        return $this->hasMany(
            BiicfJobDescriptionPoint::class,
            'job_role_id'
        )->orderBy('sort_order');
    }

    /**
     * Every duty recorded for this role, at both levels of
     * nesting and of both kinds.
     *
     * Usually the wrong relation to read directly — see
     * genericWorkFunctions and specificWorkFunctions, which
     * return only the top level of each.
     */
    public function criticalWorkFunctions(): HasMany
    {
        return $this->hasMany(
            BiicfCriticalWorkFunction::class,
            'job_role_id'
        )->orderBy('sort_order');
    }

    /**
     * Duties the role performs however it is practised.
     */
    public function genericWorkFunctions(): HasMany
    {
        return $this->criticalWorkFunctions()
            ->generic()
            ->topLevel();
    }

    /**
     * Duties belonging to one of the role's job families.
     *
     * Grouping these by family is what reproduces the book's
     * layout, where each family heads its own list.
     */
    public function specificWorkFunctions(): HasMany
    {
        return $this->criticalWorkFunctions()
            ->specific()
            ->topLevel();
    }

    /**
     * The lowest tier of this role.
     *
     * Every role has at least one, so this is never null for
     * framework data.
     */
    public function entryTier(): HasOne
    {
        return $this->hasOne(
            BiicfJobRoleTier::class,
            'job_role_id'
        )->ofMany('sort_order', 'min');
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
