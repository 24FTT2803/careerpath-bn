<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A rung on one of the framework's two proficiency scales.
 *
 * Technical competencies run over five — Follow, Assist, Apply,
 * Ensure, Strategise. Soft skills run over four — Basic,
 * Intermediate, Advanced, Expert. They share this table but not
 * their scale, and a level on one is not equivalent to the
 * level of the same number on the other.
 */
class BiicfProficiencyLevel extends Model
{
    public const SCALE_TECHNICAL = 'technical';

    public const SCALE_SOFT = 'soft';

    protected $table = 'biicf_proficiency_levels';

    protected $fillable = ['scale', 'level_number', 'name', 'description'];

    public function scopeTechnical(Builder $query): Builder
    {
        return $query->where('scale', self::SCALE_TECHNICAL);
    }

    public function scopeSoft(Builder $query): Builder
    {
        return $query->where('scale', self::SCALE_SOFT);
    }

    /**
     * How many rungs this level's own scale has.
     *
     * Used when showing a level as a proportion, so a soft skill
     * at Advanced reads as 3 of 4 rather than 3 of 5.
     */
    public function scaleLength(): int
    {
        return $this->scale === self::SCALE_SOFT ? 4 : 5;
    }

    public function descriptors(): HasMany
    {
        return $this->hasMany(
            BiicfCompetencyLevelDescriptor::class,
            'proficiency_level_id'
        );
    }
}
