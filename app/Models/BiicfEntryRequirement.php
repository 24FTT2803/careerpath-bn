<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One way of qualifying for one tier of a job role.
 *
 * A tier can have several: the book's Level 3 IT Technician
 * accepts BDQF 4 in IT, or BDQF 3 in IT with an industry
 * certification. Meeting any one alternative satisfies the tier.
 *
 * bdqf_level is the structured form used for matching and is
 * null where the book's wording names no single level.
 * raw_text always holds that wording, so the requirement can be
 * shown faithfully even when it cannot be measured.
 */
class BiicfEntryRequirement extends Model
{
    protected $table = 'biicf_entry_requirements';

    protected $fillable = [
        'job_role_id',
        'job_role_tier_id',
        'alternative_number',
        'bdqf_level',
        'field_of_study',
        'alternative_pathway',
        'years_experience',
        'raw_text',
        'requires_industry_certification',
        'requires_industry_experience',
    ];

    /**
     * Appended so the explorer, which reads roles as JSON, gets
     * something printable without having to decide between the
     * structured level and the original wording itself.
     *
     * @var array<int, string>
     */
    protected $appends = ['level_label'];

    protected function casts(): array
    {
        return [
            'requires_industry_certification' => 'boolean',
            'requires_industry_experience' => 'boolean',
        ];
    }

    /**
     * The requirement's qualification level, as text.
     *
     * Prefers the structured level, falls back to the book's own
     * wording for the phrasings that name no single level, and is
     * null when there is neither.
     */
    public function getLevelLabelAttribute(): ?string
    {
        if ($this->bdqf_level !== null) {
            return 'BDQF Level '.$this->bdqf_level;
        }

        return $this->raw_text;
    }

    /**
     * Fill in the role from the tier.
     *
     * The role is kept alongside the tier so the role's own
     * relations keep working, and it is required. Deriving it
     * here means a caller working in terms of tiers — the
     * seeder, the edition import, the admin forms — cannot
     * forget to supply something it already implies.
     */
    protected static function booted(): void
    {
        static::creating(function (self $requirement): void {
            if ($requirement->job_role_id !== null) {
                return;
            }

            $requirement->job_role_id = BiicfJobRoleTier::query()
                ->whereKey($requirement->job_role_tier_id)
                ->value('job_role_id');
        });
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(
            BiicfJobRoleTier::class,
            'job_role_tier_id'
        );
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(
            BiicfJobRole::class,
            'job_role_id'
        );
    }

    /**
     * Whether this alternative can be checked against a
     * student's qualifications at all.
     *
     * A requirement phrased as a range, or carrying an
     * experience condition we hold no record of, is reported as
     * undetermined rather than passed or failed.
     */
    public function isMeasurable(): bool
    {
        return $this->bdqf_level !== null
            && ! $this->requires_industry_experience;
    }
}
