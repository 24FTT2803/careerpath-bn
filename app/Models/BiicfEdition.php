<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A published edition of the BIICF reference book.
 *
 * The live biicf_* tables hold whichever edition is current.
 * Superseded editions keep their framework in snapshot_data so
 * they stay readable, and a student's older report points at the
 * edition it was generated against.
 */
class BiicfEdition extends Model
{
    protected $table = 'biicf_editions';

    protected $fillable = [
        'name',
        'slug',
        'edition_number',
        'published_on',
        'source_file_path',
        'source_original_name',
        'applied_at',
        'is_current',
        'snapshot_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'applied_at' => 'datetime',
            'is_current' => 'boolean',
            'snapshot_data' => 'array',
        ];
    }

    /**
     * The edition students are currently seeing.
     *
     * Null before the first edition has been applied, which is
     * the state the framework is in until then.
     */
    public static function current(): ?self
    {
        return static::query()
            ->where('is_current', true)
            ->first();
    }

    /**
     * Whether this edition's framework is still live rather than
     * archived into snapshot_data.
     */
    public function isLive(): bool
    {
        return (bool) $this->is_current;
    }

    public function jobRoles(): HasMany
    {
        return $this->hasMany(
            BiicfJobRole::class,
            'source_edition_id'
        );
    }

    /**
     * Roles this edition removed from the framework.
     *
     * They are retired rather than deleted, so the
     * recommendations pointing at them survive.
     */
    public function retiredJobRoles(): HasMany
    {
        return $this->hasMany(
            BiicfJobRole::class,
            'retired_in_edition_id'
        );
    }

    /**
     * What this edition proposes to change, awaiting review.
     */
    public function changes(): HasMany
    {
        return $this->hasMany(
            BiicfEditionChange::class,
            'edition_id'
        );
    }

    /**
     * Whether every difference has been decided one way or the
     * other, which is what an edition needs before it can be
     * applied.
     */
    public function isReviewed(): bool
    {
        return $this->changes()->pending()->doesntExist();
    }
}
