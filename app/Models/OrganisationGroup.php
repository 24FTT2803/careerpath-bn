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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            OrganisationGroup::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            OrganisationGroup::class,
            'parent_id'
        );
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