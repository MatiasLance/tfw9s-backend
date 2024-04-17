<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Field extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'region'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class)->withTrashed();
    }

    public function event()
    {
        return $this->hasMany(Event::class);
    }

}
