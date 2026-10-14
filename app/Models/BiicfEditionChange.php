<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One difference an uploaded edition proposes.
 *
 * Until an administrator decides it, and until that decision is
 * applied, it changes nothing a student sees.
 */
class BiicfEditionChange extends Model
{
    protected $table = 'biicf_edition_changes';

    public const KIND_ADDED = 'added';

    public const KIND_CHANGED = 'changed';

    public const KIND_REMOVED = 'removed';

    public const DECISION_PENDING = 'pending';

    /** Take what the new edition says. */
    public const DECISION_ACCEPT = 'accept';

    /** Leave the framework as it stands. */
    public const DECISION_KEEP = 'keep';

    /** Take a value the administrator wrote instead. */
    public const DECISION_EDIT = 'edit';

    protected $fillable = [
        'edition_id',
        'subject_type',
        'subject_key',
        'subject_label',
        'field',
        'kind',
        'current_value',
        'proposed_value',
        'source_location',
        'decision',
        'edited_value',
        'decided_at',
        'decided_by',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'array',
            'proposed_value' => 'array',
            'source_location' => 'array',
            'edited_value' => 'array',
            'decided_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(BiicfEdition::class, 'edition_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('decision', self::DECISION_PENDING);
    }

    public function scopeUnapplied(Builder $query): Builder
    {
        return $query->whereNull('applied_at');
    }

    /**
     * What this change would write, once decided.
     *
     * An accepted change writes what the book says; an edited
     * one writes what the administrator wrote instead; keeping
     * the current value writes nothing at all.
     */
    public function outcome(): mixed
    {
        return match ($this->decision) {
            self::DECISION_ACCEPT => $this->proposed_value,
            self::DECISION_EDIT => $this->edited_value,
            default => null,
        };
    }

    public function isDecided(): bool
    {
        return $this->decision !== self::DECISION_PENDING;
    }

    /**
     * Whether this change would write anything.
     *
     * Keeping the current value is a decision, and a recorded
     * one — it is how an administrator says the book is wrong
     * about something they have corrected by hand — but it
     * leaves the tables alone.
     */
    public function writes(): bool
    {
        return in_array(
            $this->decision,
            [self::DECISION_ACCEPT, self::DECISION_EDIT],
            true
        );
    }
}
