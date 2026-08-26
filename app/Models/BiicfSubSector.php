<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiicfSubSector extends Model
{
    protected $table = 'biicf_sub_sectors';

    protected $fillable = ['name', 'slug', 'description', 'lead_organisation', 'sort_order'];

    public function jobRoles(): HasMany
    {
        return $this->hasMany(BiicfJobRole::class, 'sub_sector_id')->orderBy('career_path_level');
    }
}