<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BiicfTraining extends Model
{
    protected $table = 'biicf_trainings';

    protected $fillable = ['name', 'provider', 'certification_body', 'url', 'description'];

    public function jobRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_job_role_training',
            'training_id',
            'job_role_id'
        );
    }
}