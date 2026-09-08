<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsoredAccessGrant extends Model
{
    protected $fillable = [
        'business_sponsor_id',
        'plan_id',
        'organisation_id',
        'organisation_group_id',
        'starts_at',
        'ends_at',
        'is_active',
        'priority',
        'funding_type',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(
            BusinessSponsor::class,
            'business_sponsor_id'
        );
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            Plan::class
        );
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(
            Organisation::class
        );
    }

    public function organisationGroup(): BelongsTo
    {
        return $this->belongsTo(
            OrganisationGroup::class
        );
    }

    public function featureOverrides(): HasMany
    {
        return $this->hasMany(
            SponsoredAccessFeatureOverride::class
        );
    }

    public function scopeCurrentlyActive(
        Builder $query
    ): Builder {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere(
                        'starts_at',
                        '<=',
                        now()
                    );
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('ends_at')
                    ->orWhere(
                        'ends_at',
                        '>=',
                        now()
                    );
            });
    }
}