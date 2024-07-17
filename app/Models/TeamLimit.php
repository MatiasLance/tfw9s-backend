<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamLimit extends Model
{
    use HasFactory;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
        'series',
    ];

    protected $with = [
        'ageGroups',
    ];

    public function ageGroups()
    {
        return $this->belongsToMany(AgeGroup::class, 'agegroup_teamlimit', 'teamlimit_id', 'agegroup_id');
    }

    public function series()
    {
        return $this->belongsTo(Series::class)->withTrashed();
    }
}
