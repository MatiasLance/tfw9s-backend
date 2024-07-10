<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'hash',
        'path',
        'format',
        'mime_type',
        'size',
    ];

    protected $hidden = [
        'id',
        'format',
        'mime_type',
        'size',
        'created_at',
        'updated_at',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

}
