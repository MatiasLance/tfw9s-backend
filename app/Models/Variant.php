<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $hidden = [
        'order',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'elements'
    ];

    public function elements()
    {
        return $this->hasMany(Element::class);
    }
}
