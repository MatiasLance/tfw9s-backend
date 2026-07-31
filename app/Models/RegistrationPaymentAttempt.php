<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationPaymentAttempt extends Model
{
    protected $fillable = [
        'registration_key',
        'series_id',
        'gateway',
        'transaction_id',
        'status',
    ];
}
