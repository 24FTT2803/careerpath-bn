<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerAdviserMessage extends Model
{
    /**
     * Sent by the student.
     */
    public const ROLE_USER = 'user';

    /**
     * Returned by the Career Adviser.
     */
    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'career_adviser_conversation_id',
        'recommendation_generation_id',
        'role',
        'content',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            CareerAdviserConversation::class,
            'career_adviser_conversation_id'
        );
    }

    /**
     * The recommendation generation that was active when this
     * message was sent, if the student had one.
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(
            RecommendationGeneration::class,
            'recommendation_generation_id'
        );
    }

    public function isFromStudent(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
