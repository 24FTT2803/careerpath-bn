<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One bullet of a job role's description.
 *
 * The book writes a job description as a short list of what the
 * holder does, not a paragraph.
 */
class BiicfJobDescriptionPoint extends Model
{
    protected $table = 'biicf_job_description_points';

    protected $fillable = [
        'job_role_id',
        'text',
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
