<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A job level in the framework's own vocabulary — Entrant,
 * Specialist, Expert/Management, Senior Management, C-Suite.
 *
 * Seniority of a position, not proficiency in a competency. The
 * two scales are unrelated and must not be compared.
 */
class BiicfJobLevel extends Model
{
    protected $table = 'biicf_job_levels';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'description',
    ];

    public function jobRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_job_role_job_level',
            'job_level_id',
            'job_role_id'
        );
    }

    /**
     * Role tiers that are this job level.
     *
     * Only tiers taken from a role's Job Level appear here;
     * numbered tiers within a single level have no job level of
     * their own.
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(
            BiicfJobRoleTier::class,
            'job_level_id'
        );
    }
}
