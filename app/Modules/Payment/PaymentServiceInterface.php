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
     * Percentage of GST. Must be 0-1, 1 being 100%
     *
     * @var float GST
     */
    public const GST = 0.1;

    /**
     * Initiate a new order for the selected payment gateway
     *
     * @param string $gateway Payment gateway to use
     * @param array $items List of items and item quantity
     * @param array $metadata Metadata about the order
     */
    public function createOrder($discountcode, string $gateway, array $items, array $metadata = [], $currency = null);

    /**
     * Verify a payment intent and check its status.
     *
     * @param string $gateway Payment gateway to use
     * @param string $transactionId
     */
    public function verify(string $gateway, string $transactionId);

    /**
     * Initiate a new order for the selected payment gateway
     *
     * @param string $gateway Payment gateway to use
     * @param array $metadata Metadata about the order
     */
    public function createindividualregistration($discountcode, string $gateway, string $item, array $metadata = [], $currency = null);

    /**
     * Update a payment intent
     *
     * @param string $paymentIntent ID to use
     * @param array $seriesItem
     * @param string gateway
     */
    public function updateAmount(string $paymentIntent, array $seriesItem, string $gateway);

    /**
     * Verify a payment intent and check its status.
     *
     * @param string $gateway Payment gateway to use
     * @param string $transactionId
     */
    public function verifyIndividualRegistration(string $gateway, string $transactionId);

    /**
     * Verify a payment intent and check its status.
     *
     * @param string $gateway Payment gateway to use
     * @param string $transactionId
     */
    public function verifyTeamRegistration(string $gateway, string $transactionId);
}
