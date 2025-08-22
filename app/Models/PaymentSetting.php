<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_enabled',
        'afterpay_enabled'
    ];

    protected $casts = [
        'stripe_enabled' => 'boolean',
        'afterpay_enabled' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
