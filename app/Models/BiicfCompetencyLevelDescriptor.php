<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'scope_label',
        'source_edition_id',
        'source_page',
    ];

    protected function casts(): array
    {
        return [
            'statements' => 'array',
        ];
    }

    /**
     * Whether this descriptor applies only to particular roles.
     *
     * The book describes Infrastructure Management differently
     * for an IT Technician than for a Systems Engineer, and
     * does the same for three other competencies. An unscoped
     * descriptor applies wherever its competency is required.
     */
    public function isScoped(): bool
    {
        return $this->scope_label !== '' && $this->scope_label !== null;
    }

    /**
     * The roles a scoped descriptor covers.
     *
     * Resolved from the scope text once job roles are loaded, so
     * an empty set alongside a scope label means the roles have
     * not been matched up yet rather than that none apply.
     */
    public function jobRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_competency_descriptor_job_role',
            'descriptor_id',
            'job_role_id'
        );
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
