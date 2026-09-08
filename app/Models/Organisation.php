<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisation extends Model
{
    protected $fillable = [
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