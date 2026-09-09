<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganisationGroupType extends Model
{
    protected $fillable = [
        'organisation_id',
        'name',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(
            Organisation::class
        );
    }

    public function groups(): HasMany
    {
        return $this->hasMany(
            OrganisationGroup::class,
            'group_type_id'
        );
    }
}