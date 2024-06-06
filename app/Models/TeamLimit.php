<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamLimit extends Model
{
    use HasFactory;

    protected $with = [
        'ageGroups',
    ];

    public function ageGroups()
    {
        return $this->belongsToMany(AgeGroup::class, 'agegroup_teamlimit', 'teamlimit_id', 'agegroup_id');
    }
}
