<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A sign-up whose address has not been authenticated yet.
 *
 * It holds what the form collected, with the password already
 * hashed, and becomes a User only when the link sent to the
 * address is used. The link carries a token; only its hash is
 * kept, so the table alone cannot be used to authenticate.
 */
class PendingRegistration extends Model
{
    protected $fillable = [
        'name',
        'email',
        'programme',
        'password',
        'student_id',
        'token_hash',
        'expires_at',
    ];

    protected $hidden = [
        'password',
        'token_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function tokenMatches(string $token): bool
    {
        return hash_equals($this->token_hash, self::hashToken($token));
    }

    public function hasExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }
}
