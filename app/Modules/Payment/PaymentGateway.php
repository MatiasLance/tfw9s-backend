<?php

namespace App\Modules\Payment;

use App\Modules\Payment\Gateways\Paypal;
use App\Modules\Payment\Gateways\Square;
use App\Modules\Payment\Gateways\Stripe;

enum PaymentGateway: string
{
    /**
     * Stripe payment gateway
     */
    case STRIPE = 'stripe';

    /**
     * Paypal payment gateway
     */
    case PAYPAL = 'paypal';

    /**
     * Square payment gateway
     */
    case SQUARE = 'square';

    /**
     * Retrieve the associated `App\Modules\Payment\Gateways\PaymentGatewayInterface` class with the gateway
     * 
     * @return string
     */
    public function getGatewayClass(): string
    {
        return match($this)
        {
            self::STRIPE => Stripe::class,
            self::PAYPAL => Paypal::class,
            self::SQUARE => Square::class,
        };
    }
}