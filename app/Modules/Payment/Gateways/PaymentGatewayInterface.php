<?php

namespace App\Modules\Payment\Gateways;

interface PaymentGatewayInterface
{
    /**
     * Payment method indicating stripe
     *
     * @var string PAYMENT_METHOD_STRIPE
     */
    public const PAYMENT_METHOD_STRIPE = 'stripe';

    /**
     * Payment method indicating paypal
     *
     * @var string PAYMENT_METHOD_PAYPAL
     */
    public const PAYMENT_METHOD_PAYPAL = 'paypal';

    /**
     * Create a new order request
     * 
     * @param array $items List of items and their quantities
     * @param array $metadata Metadata regarding the order
     */
    public function createOrder(array $items, array $metadata = []);

    /**
     * Verify transaction status
     * 
     * @param string $transactionId
     */
    public function verify(string $transactionId);
}