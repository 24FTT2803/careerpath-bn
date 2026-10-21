<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One language a job role is expected to know.
 *
 * The book sets these as a section of their own, not as
 * recommended courses: the role is expected to have the
 * language, and the framework names no training for it.
 */
class BiicfJobProgrammingLanguage extends Model
{
    protected $table = 'biicf_job_programming_languages';

    protected $fillable = [
        'job_role_id',
        'name',
        'sort_order',
    ];

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(
            BiicfJobRole::class,
            'job_role_id'
        );
    }
}
