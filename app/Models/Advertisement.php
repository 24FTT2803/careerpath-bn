<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Advertisement extends Model
{
    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    public const TYPE_LINK = 'link';

    /**
     * An embed supplied by an advertising network.
     */
    public const TYPE_NETWORK = 'network';

    /**
     * Positions relocate by viewport rather than disappearing,
     * so a page shows the same number of advertisements on a
     * phone as it does on a desktop.
     */
    public const POSITION_ONE = 'one';

    public const POSITION_TWO = 'two';

    protected $fillable = [
        'title',
        'type',
        'asset_path',
        'video_crop',
        'external_url',
        'click_url',
        'alt_text',
        'position',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
        'organisation_group_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'video_crop' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function organisationGroup(): BelongsTo
    {
        return $this->belongsTo(
            OrganisationGroup::class
        );
    }

    /**
     * Active and inside its scheduled window.
     */
    public function scopeCurrentlyRunning(
        Builder $query
    ): Builder {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Where the media actually lives.
     *
     * Uploaded assets resolve to a storage URL; everything else
     * points outward.
     */
    public function mediaUrl(): ?string
    {
        if ($this->asset_path) {
            return Storage::url($this->asset_path);
        }

        return $this->external_url;
    }

    /**
     * What is actually true of this advertisement right now.
     *
     * Derived rather than stored. An advertisement switched on
     * but dated for next month is not running, and saying
     * "active" in that case is how an administrator concludes
     * the system is broken.
     */
    public function status(): string
    {
        if (! $this->is_active) {
            return 'paused';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'ended';
        }

        return 'live';
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    /**
     * A chosen part of the picture, as fractions of it, or null
     * when anything is missing or out of range.
     *
     * @return array{x: float, y: float, w: float, h: float}|null
     */
    public static function cleanVideoCrop(mixed $crop): ?array
    {
        if (is_string($crop)) {
            $crop = json_decode($crop, true);
        }

        if (! is_array($crop)) {
            return null;
        }

        $values = [];

        foreach (['x', 'y', 'w', 'h'] as $key) {
            if (! isset($crop[$key]) || ! is_numeric($crop[$key])) {
                return null;
            }

            $values[$key] = round((float) $crop[$key], 5);
        }

        if (
            $values['x'] < 0
            || $values['y'] < 0
            || $values['w'] <= 0.01
            || $values['h'] <= 0.01
            || $values['x'] + $values['w'] > 1.001
            || $values['y'] + $values['h'] > 1.001
        ) {
            return null;
        }

        /* The whole picture is the same as no frame at all. */
        if ($values['w'] >= 0.999 && $values['h'] >= 0.999) {
            return null;
        }

        return $values;
    }

    /**
     * Where the video sits inside the banner's window so that
     * only the chosen part shows: its size and offset, in
     * percent of the window. Null shows the whole video,
     * filling the window.
     *
     * @return array{width: float, height: float, left: float, top: float}|null
     */
    public function videoFrame(): ?array
    {
        if (! $this->isVideo()) {
            return null;
        }

        $crop = self::cleanVideoCrop($this->video_crop);

        if ($crop === null) {
            return null;
        }

        return [
            'width' => round(100 / $crop['w'], 3),
            'height' => round(100 / $crop['h'], 3),
            'left' => round(-100 * $crop['x'] / $crop['w'], 3),
            'top' => round(-100 * $crop['y'] / $crop['h'], 3),
        ];
    }

    public function isNetworkEmbed(): bool
    {
        return $this->type === self::TYPE_NETWORK;
    }
}
