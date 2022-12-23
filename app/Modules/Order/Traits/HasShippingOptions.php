<?php

namespace App\Modules\Order\Traits;

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
        try {
            return ShippingType::from($shippingType)
                                    ->getShippingNote();
        } catch (\TypeError $e) {
            throw new UnknownShippingTypeException('Shipping type: ' . $shippingType . ' is unsupported.');
        }
    }
}