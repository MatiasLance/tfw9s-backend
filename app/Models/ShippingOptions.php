<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingOptions extends Model
{
    use HasFactory;

    public const DEFAULT_DELIVERY_CHARGE = 1000;

    public const DEFAULT_PICKUP_CHARGE = 0;

    protected $fillable = [
        'delivery_note',
        'pickup_note',
        'delivery_charge',
        'pickup_charge',
    ];

    protected $casts = [
        'delivery_charge' => 'integer',
        'pickup_charge' => 'integer',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];
}
