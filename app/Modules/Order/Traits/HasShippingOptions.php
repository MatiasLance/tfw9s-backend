<?php

namespace App\Modules\Order\Traits;

use App\Models\ShippingOptions;
use App\Modules\Order\Exceptions\UnknownShippingTypeException;
use App\Modules\Order\ShippingType;

trait HasShippingOptions
{
    /**
     * Get the corresponding shipping note of the given shipping type
     * 
     * @param string $shippingType
     * 
     * @return string
     */
    public function getShippingNote(string $shippingType): string
    {
        $options = ShippingOptions::first();
        switch ($shippingType) {
            case ShippingType::DELIVERY:
                return $options->delivery_note;
                break;

            case ShippingType::PICKUP:
                return $options->pickup_note;
                break;
            
            default:
                throw new UnknownShippingTypeException();
                break;
        }
    }
}