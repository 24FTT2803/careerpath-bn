<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpbnBotMessage extends Model
{
    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'role',
        'content',
        'links',
        'tokens',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
            'tokens' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CpbnBotConversation::class, 'cpbn_bot_conversation_id');
    }

    public function isFromStudent(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
