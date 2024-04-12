<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $with = [
        'media'
    ];

    public function media()
    {
        return $this->morphMany('App\Models\Media', 'imageable');
    }

}
