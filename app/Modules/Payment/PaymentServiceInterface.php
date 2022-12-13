<?php

namespace App\Modules\Payment;

interface PaymentServiceInterface
{

    /**
     * Default currency to use for transactions
     * 
     * @var string CURRENCY
     */
    public const CURRENCY = 'aud';
    
    /**
     * Initiate a new order for the selected payment gateway
     * 
     * @param string $gateway Payment gateway to use
     * @param array $items List of items and item quantity
     * @param array $metadata Metadata about the order
     */
    public function createOrder(string $gateway, array $items, array $metadata = [], $currency = null);

    /**
     * Verify a payment intent and check its status.
     * 
     * @param string $gateway Payment gateway to use
     * @param string $transactionId
     */
    public function verify(string $gateway, string $transactionId);
}