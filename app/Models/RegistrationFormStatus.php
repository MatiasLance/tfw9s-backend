<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationFormStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'is_show_count_down_timer',
        'date'
    ];

    protected $casts = [
        'is_show_count_down_timer' => 'boolean',
        'date' => 'date',
        'series_id' => 'integer'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
