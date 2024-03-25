<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    
    protected $with = [
        'field'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

}