<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(
            PlanFeature::class
        );
    }

    public function grants(): HasMany
    {
        return $this->hasMany(
            UserPlanGrant::class
        );
    }
}