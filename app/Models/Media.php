<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'mediable_id',
        'mediable_type',
        'format',
        'mime_type',
        'size',
        'created_at',
        'updated_at',
    ];

    public function mediable()
    {
        return $this->morphTo();
    }
}
