<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What one competency means at one of its proficiency levels.
 *
 * Shared reference data rather than per-role, so every role
 * requiring Application Development at Assist shows the same
 * statements. A missing row is the book's N/A — the competency
 * does not reach that level at all — which is not the same as a
 * row with no statements.
 */
class BiicfCompetencyLevelDescriptor extends Model
{
    protected $table = 'biicf_competency_level_descriptors';

    protected $fillable = [
        'competency_id',
        'proficiency_level_id',
        'statements',
        'source_edition_id',
        'source_page',
    ];

    protected function casts(): array
    {
        return [
            'statements' => 'array',
        ];
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(
            BiicfCompetency::class,
            'competency_id'
        );
    }

    public function proficiencyLevel(): BelongsTo
    {
        return $this->belongsTo(
            BiicfProficiencyLevel::class,
            'proficiency_level_id'
        );
    }

    public function sourceEdition(): BelongsTo
    {
        return $this->belongsTo(
            BiicfEdition::class,
            'source_edition_id'
        );
    }
}
