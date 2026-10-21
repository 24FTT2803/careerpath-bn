<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A job family — the framework's narrower grouping inside a
 * functional group, such as "Server and network support".
 *
 * A role can sit in several, and the book groups a role's
 * specific critical work functions under the family they belong
 * to.
 */
class BiicfJobFamily extends Model
{
    protected $table = 'biicf_job_families';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function jobRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            BiicfJobRole::class,
            'biicf_job_role_job_family',
            'job_family_id',
            'job_role_id'
        )->withPivot('sort_order');
    }
}
