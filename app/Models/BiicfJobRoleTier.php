<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Clear what belongs to this tier when it goes.
     *
     * Neither the entry requirements nor the competency pivot
     * carries a database foreign key to the tier, because SQLite
     * cannot drop a column that has one and those columns have
     * to stay reversible. So the cascade lives here instead of
     * in the schema, and anything deleting a tier must do it
     * through the model rather than a bulk query.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $tier): void {
            $tier->entryRequirements()->delete();
            $tier->competencies()->detach();
        });
    }

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
     * The ways of qualifying for this tier.
     *
     * Several rows mean several alternatives, any one of which
     * is enough.
     */
    public function entryRequirements(): HasMany
    {
        return $this->hasMany(
            BiicfEntryRequirement::class,
            'job_role_tier_id'
        )->orderBy('alternative_number');
    }

    /**
     * This tier's column of the role's competency table: which
     * competencies it needs, and at what proficiency.
     */
    public function competencies(): BelongsToMany
    {
        $relation = $this->belongsToMany(
            BiicfCompetency::class,
            'biicf_job_role_competency',
            'job_role_tier_id',
            'competency_id'
        )->withPivot('proficiency_level_id', 'is_core');

        /*
         * The pivot also carries the role. It is derivable from
         * the tier, but kept so the role's own competencies
         * relation still works, and filled in here so a caller
         * attaching through a tier cannot forget it.
         *
         * Only when this instance actually knows its role.
         * Eager loading builds the query from a blank model, and
         * withPivotValue rejects a null — which is how this
         * broke every eager load of tiers.competencies.
         */
        if ($this->job_role_id !== null) {
            $relation->withPivotValue('job_role_id', $this->job_role_id);
        }

        return $relation;
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
