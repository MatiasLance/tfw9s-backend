<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'media',
    ];

    public function event()
    {
        return $this->hasMany(Event::class);
    }

    public function team()
    {
        return $this->hasMany(Team::class);
    }

    public function media()
    {
        return $this->morphMany('App\Models\Media', 'imageable');
    }

    public function centPrice()
    {
        return $this->getAttributes()['price'];
    }

    public function individualRegistration()
    {
        return $this->hasMany(IndividualRegistration::class);
    }

    public function teamRegistration()
    {
        return $this->hasMany(TeamRegistration::class);
    }
}
