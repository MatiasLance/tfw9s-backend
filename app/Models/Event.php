<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $with = [
        'event_matches',
        'field'

    ];

    public function event_matches()
    {
        return $this->hasMany(EventMatch::class);
    }
}
