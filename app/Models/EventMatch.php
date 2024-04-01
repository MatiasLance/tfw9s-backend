<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMatch extends Model
{
    use HasFactory;

    protected $with = [
        'team1',
        'team2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2');
    }

    public function event_match_video()
    {
        return $this->hasOne(EventMatchVideo::class);
    }

    public function media()
    {
        return $this->morphOne('App\Models\Media', 'imageable');
    }
}
