<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMembership extends Model
{
    protected $fillable = [
        'user_id',
        'organisation_group_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function organisationGroup(): BelongsTo
    {
        return $this->belongsTo(
            OrganisationGroup::class
        );
    }
}