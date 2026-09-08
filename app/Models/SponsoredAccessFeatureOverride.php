<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsoredAccessFeatureOverride extends Model
{
    protected $fillable = [
        'sponsored_access_grant_id',
        'feature_definition_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public function sponsoredAccessGrant(): BelongsTo
    {
        return $this->belongsTo(
            SponsoredAccessGrant::class
        );
    }

    public function featureDefinition(): BelongsTo
    {
        return $this->belongsTo(
            FeatureDefinition::class
        );
    }
}