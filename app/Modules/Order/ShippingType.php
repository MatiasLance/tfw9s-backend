<?php

namespace App\Modules\Order;

use App\Models\ShippingOptions;

enum ShippingType: string
{
    case DELIVERY = 'delivery';
    case PICKUP = 'pickup';

    public function getShippingNote(): string
    {
        $option = ShippingOptions::first();

        return match($this)
        {
            self::DELIVERY => $option->delivery_note,
            self::PICKUP => $option->pickup_note,
        };
    }
}