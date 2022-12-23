<?php

namespace App\Modules\Payment;

interface PaymentServiceInterface
{
    /**
     * Default currency to use for transaction
     * 
     * @var string CURRENCY
     */
    public const CURRENCY = 'aud';
    
    /**
     * Create a new payment intent for custom payment flow
     * 
     * @param array $items List of items and item quantity
     * @param array $metadata Metadata to associate with the Payment Intent
     * @param string $currency (Optional) If null, will use default currency
     * 
     * @return string
     */
    public function createPaymentIntent(array $items, array $metadata = [], $currency = null): array;

    /**
     * Verify a payment intent and check its status.
     * 
     * @param string $paymentIntentId
     */
    public function verify(string $paymentIntentId);
}