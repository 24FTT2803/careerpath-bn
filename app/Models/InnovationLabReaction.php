<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One emoji a student put on one post.
 *
 * A student may add several different ones to the same post, so
 * what identifies a reaction is the post, the student and the
 * emoji together.
 */
class InnovationLabReaction extends Model
{
    protected $table = 'innovation_lab_reactions';

    protected $fillable = [
        'note_id',
        'user_id',
        'emoji',
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
