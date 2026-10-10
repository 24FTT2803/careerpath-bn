<?php

namespace App\Models\Concerns;

use App\Models\BiicfEdition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared behaviour for framework data that can come either from
 * an imported edition or from an administrator typing it in.
 *
 * Four tables carry this — sub-sectors, job roles, competencies
 * and trainings — and an import has to treat the two origins
 * differently. A hand-added role is not offered for retirement
 * just because a new book omits it, and a hand-edited field is
 * raised as a conflict rather than quietly overwritten.
 *
 * The provenance columns are merged into $fillable here so each
 * model's own list stays about its own columns.
 */
trait HasEditionProvenance
{
    public function initializeHasEditionProvenance(): void
    {
        $this->mergeFillable([
            'origin',
            'source_edition_id',
            'source_page',
            'retired_in_edition_id',
            'hand_edited_at',
            'hand_edited_by',
        ]);

        $this->mergeCasts([
            'hand_edited_at' => 'datetime',
        ]);
    }

    public function sourceEdition(): BelongsTo
    {
        return $this->belongsTo(
            BiicfEdition::class,
            'source_edition_id'
        );
    }

    public function retiredInEdition(): BelongsTo
    {
        return $this->belongsTo(
            BiicfEdition::class,
            'retired_in_edition_id'
        );
    }

    public function handEditedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'hand_edited_by'
        );
    }

    /**
     * Entries still part of the framework students are shown.
     *
     * Retired entries stay in the table so the recommendations
     * and reports referring to them keep working, but they are
     * no longer part of the current framework.
     */
    public function scopeInCurrentFramework(Builder $query): Builder
    {
        return $query->whereNull('retired_in_edition_id');
    }

    public function scopeRetired(Builder $query): Builder
    {
        return $query->whereNotNull('retired_in_edition_id');
    }

    public function isRetired(): bool
    {
        return $this->retired_in_edition_id !== null;
    }

    public function isImported(): bool
    {
        return $this->origin === 'imported';
    }

    public function isManual(): bool
    {
        return $this->origin === 'manual';
    }

    /**
     * An imported entry that has since been edited by hand, and
     * so needs a decision rather than an overwrite next time an
     * edition touches it.
     */
    public function wasHandEdited(): bool
    {
        return $this->isImported() && $this->hand_edited_at !== null;
    }
}
