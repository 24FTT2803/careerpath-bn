<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiicfProficiencyLevel extends Model
{
    protected $table = 'biicf_proficiency_levels';

    protected $fillable = ['level_number', 'name', 'description'];
}