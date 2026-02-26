<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventMatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'match_time',
        'field_id',
        'team1',
        'team2',
        'submitted',
    ];

    protected $with = [
        'team1',
        'team2',
        'field',
    ];

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1')->withTrashed();
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2')->withTrashed();
    }

    public function field()
    {
        return $this->belongsTo(Field::class)->withTrashed();
    }

    public function event_match_video()
    {
        return $this->hasOne(EventMatchVideo::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

}
