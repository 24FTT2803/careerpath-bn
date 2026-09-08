<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureUsage extends Model
{
    protected $fillable = [
        'user_id',
        'feature_key',
        'access_source',
        'plan_id',
        'grant_id',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            Plan::class
        );
    }
}