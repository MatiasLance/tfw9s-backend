<?php

namespace App\Modules\Payment;

use App\Modules\Payment\Gateways\Afterpay;
use App\Modules\Payment\Gateways\Paypal;
use App\Modules\Payment\Gateways\Square;
use App\Modules\Payment\Gateways\Stripe;

enum PaymentGateway: String
{
    /**
     * Stripe payment gateway
     * 
     * Application does not currently use stripe
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
     * Afterpay payment gateway
     */
    case AFTERPAY = 'afterpay';


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
            self::AFTERPAY => Afterpay::class,
        };
    }
}