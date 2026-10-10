<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One rung of the Brunei Darussalam Qualifications Framework,
 * as set out in Annex 1 of the BIICF.
 *
 * Entry requirements are written in these levels, so this is
 * what makes "BDQF Level 4 in IT or related field" mean
 * something to a student reading it.
 */
class BiicfBdqfLevel extends Model
{
    protected $table = 'biicf_bdqf_levels';

    protected $fillable = [
        'level',
        'schools_sector',
        'tvet_sector',
        'higher_education_sector',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'schools_sector' => 'array',
            'tvet_sector' => 'array',
            'higher_education_sector' => 'array',
        ];
    }

    /**
     * Every qualification at this level, across all three
     * sectors, with duplicates removed.
     *
     * The Annex lists some awards under more than one sector —
     * an Advanced Diploma appears under both vocational and
     * higher education — and a reader asking "what is level 5?"
     * wants the award named once.
     *
     * @return array<int, string>
     */
    public function qualifications(): array
    {
        return array_values(array_unique(array_merge(
            $this->schools_sector ?? [],
            $this->tvet_sector ?? [],
            $this->higher_education_sector ?? [],
        )));
    }
}
