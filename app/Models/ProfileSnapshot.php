<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'snapshot_hash',
        'snapshot_data',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Generations produced from this profile state.
     *
     * A student who generates twice without editing anything
     * reuses one snapshot, so a snapshot may back more than one
     * generation.
     */
    public function recommendationGenerations(): HasMany
    {
        return $this->hasMany(
            RecommendationGeneration::class
        );
    }
}
