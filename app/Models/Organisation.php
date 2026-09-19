<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisation extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'root_group_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The group this organisation's structure hangs from.
     */
    public function rootGroup(): BelongsTo
    {
        return $this->belongsTo(
            OrganisationGroup::class,
            'root_group_id'
        );
    }

    public function groupTypes(): HasMany
    {
        return $this->hasMany(
            OrganisationGroupType::class
        );
    }

    public function groups(): HasMany
    {
        return $this->hasMany(
            OrganisationGroup::class
        );
    }

    public function sponsoredAccessGrants(): HasMany
    {
        return $this->hasMany(
            SponsoredAccessGrant::class
        );
    }
}
