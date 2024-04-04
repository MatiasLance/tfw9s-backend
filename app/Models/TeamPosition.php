<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPosition extends Model
{
    use HasFactory;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'team',

    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

}
