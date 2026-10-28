<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One qualification a student holds or is working towards.
 *
 * A student can have several — a school leaving certificate, the
 * diploma they are on now — which is what entry requirements are
 * matched against. bdqf_level is what puts a qualification on the
 * framework's ladder; it is null where nobody has said which
 * rung the award sits on, and a match reports that rather than
 * assuming the bottom.
 */
class AcademicRecord extends Model
{
    protected $fillable = [
        'user_id',
        'institution_name',
        'programme_name',
        'level',
        'bdqf_level',
        'field_of_study',
        'start_date',
        'end_date',
        'cgpa',
        'subjects',
        'grades',
        'achievements',
        'is_current',
        'is_programme_record',
    ];

    protected function casts(): array
    {
        return [
            'bdqf_level' => 'integer',
            'is_programme_record' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'cgpa' => 'float',
            'subjects' => 'array',
            'grades' => 'array',
            'is_current' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Qualifications the student has been awarded.
     *
     * A programme still being studied is not one a role can be
     * entered with, however far along it is.
     */
    public function scopeAwarded(Builder $query): Builder
    {
        return $query->where('is_current', false);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function isAwarded(): bool
    {
        return ! $this->is_current;
    }
}
