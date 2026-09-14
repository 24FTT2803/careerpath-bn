<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganisationGroup extends Model
{
    protected $fillable = [
        'organisation_id',
        'group_type_id',

        /*
         * Listed so that assigning it reaches the mutator below
         * and fails loudly. A non-fillable key is dropped in
         * silence, which is how a group would lose its parent
         * without anyone noticing.
         */
        'parent_id',

        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Refuse a parent passed as an attribute.
     *
     * Parents live in organisation_group_parents now. Without
     * this, older code assigning parent_id would be silently
     * ignored and the group would quietly lose its place in the
     * structure.
     */
    public function setParentIdAttribute($value): void
    {
        throw new \LogicException(
            'A group parent is set through parents(), not parent_id.'
        );
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(
            Organisation::class
        );
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            OrganisationGroupType::class,
            'group_type_id'
        );
    }

    /**
     * Groups this one sits inside.
     *
     * A class belongs to its programme and to its intake
     * session at the same time, so this is a set rather than a
     * single record.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganisationGroup::class,
            'organisation_group_parents',
            'group_id',
            'parent_id'
        )
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganisationGroup::class,
            'organisation_group_parents',
            'parent_id',
            'group_id'
        )
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * The one parent used wherever a single path is needed,
     * such as a breadcrumb or a report heading.
     */
    public function primaryParent(): ?OrganisationGroup
    {
        return $this->parents()
            ->wherePivot('is_primary', true)
            ->first();
    }

    /**
     * Name the group with its primary path above it.
     */
    public function pathLabel(): string
    {
        $names = [$this->name];
        $current = $this->primaryParent();
        $seen = [$this->id => true];

        while ($current && ! isset($seen[$current->id])) {
            $seen[$current->id] = true;
            array_unshift($names, $current->name);
            $current = $current->primaryParent();
        }

        return implode(' › ', $names);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(
            GroupMembership::class
        );
    }

    public function sponsoredAccessGrants(): HasMany
    {
        return $this->hasMany(
            SponsoredAccessGrant::class
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'group_memberships'
        )->withTimestamps();
    }
}
