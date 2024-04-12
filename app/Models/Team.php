<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;

class Team extends Model
{
    use HasFactory;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'field',
        'media',
        'agegroup',
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function eventMatches()
    {
        return $this->hasMany(EventMatch::class);
    }

    public function media()
    {
        return $this->morphMany('App\Models\Media', 'imageable');
    }

    public function agegroup()
    {
        return $this->belongsTo(AgeGroup::class);
    }

}
