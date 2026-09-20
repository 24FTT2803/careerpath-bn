<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvertisementSlot extends Model
{
    /**
     * The most advertisements one slot may rotate through.
     *
     * Every creative in the rotation is loaded with the page,
     * so this is a limit on what a student downloads rather
     * than an arbitrary number.
     */
    public const MAX_ROTATION_SIZE = 5;

    protected $fillable = [
        'position',
        'name',
        'rotation_enabled',
        'rotation_size',
        'dwell_seconds',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rotation_enabled' => 'boolean',
            'is_active' => 'boolean',
            'rotation_size' => 'integer',
            'dwell_seconds' => 'integer',
        ];
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(
            Advertisement::class,
            'position',
            'position'
        );
    }

    /**
     * How many advertisements this slot should resolve.
     *
     * A slot that is not rotating shows one, whatever its
     * rotation size happens to say, so switching rotation off
     * does not require resetting the number first.
     */
    public function resolveCount(): int
    {
        if (! $this->rotation_enabled) {
            return 1;
        }

        return max(
            1,
            min($this->rotation_size, self::MAX_ROTATION_SIZE)
        );
    }
}
