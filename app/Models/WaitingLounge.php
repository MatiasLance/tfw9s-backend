<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingLounge extends Model
{
    use HasFactory;

    protected $table = 'waiting_lounge';
    protected $fillable = [
     'series_id',
     'client_id',
     'expires_at'
    ];
}
