<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'field',
        'manager',
        'agegroup',
        'eventmatch'

    ];

    public function field()
    {
        return $this->belongsTo(Field::class)->withTrashed();
    }
    public function manager()
    {
        return $this->belongsTo(Manager::class)->withTrashed();
    }
    public function agegroup()
    {
        return $this->belongsTo(AgeGroup::class)->withTrashed();
    }
    public function eventmatch()
    {
        return $this->hasMany(EventMatch::class);
    }

}
