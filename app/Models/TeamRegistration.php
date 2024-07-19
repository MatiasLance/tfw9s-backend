<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamRegistration extends Model
{
    use HasFactory;

    // protected $appends = ['teams_with_agegroups'];

    public function getOrderNumberAttribute()
    {
        return sprintf('%03d', $this->id);
    }

    public function teams() {
        return $this->hasMany(Team::class, 'registration_id');
    }

    public function item()
    {
        return $this->belongsTo(Series::class, 'item_id');
    }
    // Method to retrieve teams with agegroups
    // public function getTeamsWithAgegroupsAttribute()
    // {
    //     return $this->teams()->with('agegroup')->get();
    // }
}
