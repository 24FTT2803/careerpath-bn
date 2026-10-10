<?php

namespace App\Models;

use App\Models\Concerns\HasEditionProvenance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiicfCompetency extends Model
{
    use HasEditionProvenance;

    protected $table = 'biicf_competencies';

    protected $fillable = ['name', 'slug', 'type', 'description'];

    public function jobRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_job_role_competency',
            'competency_id',
            'job_role_id'
        )->withPivot('proficiency_level_id', 'is_core', 'job_role_tier_id');
    }

    /**
     * What this competency means at each of its levels.
     *
     * A level the book marks N/A has no row here at all, so the
     * absence is the statement.
     */
    public function levelDescriptors(): HasMany
    {
        return $this->hasMany(
            BiicfCompetencyLevelDescriptor::class,
            'competency_id'
        );
    }

    /**
     * Which proficiency scale this competency is rated on.
     *
     * Technical competencies use the five-level scale, soft
     * skills the four-level one.
     */
    public function scale(): string
    {
        return $this->type === 'soft_skill'
            ? BiicfProficiencyLevel::SCALE_SOFT
            : BiicfProficiencyLevel::SCALE_TECHNICAL;
    }
}
