<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiicfEntryRequirement extends Model
{
    protected $table = 'biicf_entry_requirements';

    protected $fillable = [
        'job_role_id', 'bdqf_level', 'field_of_study', 'alternative_pathway', 'years_experience',
    ];

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(BiicfJobRole::class, 'job_role_id');
    }
}