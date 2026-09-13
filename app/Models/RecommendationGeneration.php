<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecommendationGeneration extends Model
{
    /**
     * Newest generation, still matching the student's profile.
     */
    public const STATUS_CURRENT = 'current';

    /**
     * Superseded by a newer generation, profile unchanged.
     */
    public const STATUS_PREVIOUS = 'previous';

    /**
     * The profile changed after this generation was produced.
     * Set once profile snapshots exist.
     */
    public const STATUS_OUTDATED = 'outdated';

    /**
     * Recorded when the producing client was not captured,
     * which applies only to generations backfilled from rows
     * written before Task 6.
     */
    public const DRIVER_UNKNOWN = 'unknown';

    protected $fillable = [
        'user_id',
        'generation_number',
        'status',
        'driver',
        'schema_version',
        'recommendation_count',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generation_number' => 'integer',
            'recommendation_count' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(
            CareerRecommendation::class
        )->orderBy('rank');
    }

    public function isCurrent(): bool
    {
        return $this->status === self::STATUS_CURRENT;
    }

    public function isPrevious(): bool
    {
        return $this->status === self::STATUS_PREVIOUS;
    }

    public function isOutdated(): bool
    {
        return $this->status === self::STATUS_OUTDATED;
    }
}
