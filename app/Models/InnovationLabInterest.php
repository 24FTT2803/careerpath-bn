<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student saying they would like to join what a post is about.
 *
 * Present or absent, nothing else: pressed again the row goes,
 * so the count is who currently says yes rather than who ever
 * pressed it.
 */
class InnovationLabInterest extends Model
{
    protected $table = 'innovation_lab_interests';

    protected $fillable = [
        'note_id',
        'user_id',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(InnovationLabNote::class, 'note_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
