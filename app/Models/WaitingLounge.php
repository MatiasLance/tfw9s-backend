<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingLounge extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_ACTIVE = 'active';

    protected $table = 'waiting_lounge';
    protected $fillable = [
     'series_id',
     'client_id',
     'status',
     'expires_at'
    ];

    protected $casts = [
        'series_id' => 'integer',
        'expires_at' => 'datetime',
    ];
}
