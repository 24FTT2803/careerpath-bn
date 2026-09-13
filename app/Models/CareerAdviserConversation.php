<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerAdviserConversation extends Model
{
    protected $fillable = [
        'user_id',
        'message_count',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'message_count' => 'integer',
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Messages in the order they were sent.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(
            CareerAdviserMessage::class
        )->orderBy('id');
    }
}
