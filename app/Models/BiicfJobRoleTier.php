<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One column of a role's competency table.
 *
 * The book gives a role either numbered steps within a single
 * job level (IT Technician: Level 1, 2, 3) or the job levels it
 * spans (IT Administrator/Manager: Entrant, Expert/Management).
 * A tier is whichever of the two a given role uses, carrying its
 * own entry requirement and its own required proficiencies.
 */
class BiicfJobRoleTier extends Model
{
    protected $table = 'biicf_job_role_tiers';

    protected $fillable = [
        'job_role_id',
        'label',
        'job_level_id',
        'sort_order',
    ];

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(
            BiicfJobRole::class,
            'job_role_id'
        );
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(
            BiicfJobLevel::class,
            'job_level_id'
        );
    }

    /**
     * Whether this tier is one of the framework's named job
     * levels rather than a numbered step inside one.
     */
    public function isNamedJobLevel(): bool
    {
        return $this->job_level_id !== null;
    }
}
