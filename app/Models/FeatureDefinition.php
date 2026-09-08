<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureDefinition extends Model
{
    protected $fillable = [
        'key',
        'name',
        'category',
        'value_type',
        'parent_key',
        'global_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'global_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            FeatureDefinition::class,
            'parent_key',
            'key'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            FeatureDefinition::class,
            'parent_key',
            'key'
        );
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(
            PlanFeature::class,
            'key',
            'key'
        );
    }

    public function sponsoredOverrides(): HasMany
    {
        return $this->hasMany(
            SponsoredAccessFeatureOverride::class
        );
    }
}