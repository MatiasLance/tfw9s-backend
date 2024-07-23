<?php

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\PaymentStatus;

interface PaymentGatewayInterface
{
    /**
     * Create a new order request
     *
     * @param array $items List of items and their quantities
     * @param array $metadata Metadata regarding the order
     */
    public function createOrder($discountcode, array $items, array $metadata = []);

    /**
     * Verify transaction status
     *
     * @param string $transactionId
     */
    public function verify(string $transactionId): PaymentStatus;

    /**
     * Create a new order request
     *
     * @param string $item List of items and their quantities
     * @param array $metadata Metadata regarding the order
     */
    public function createIndividualRegistration($discountcode, string $item, array $metadata = []);

    /**
     *  update Amount in Payment Intent
     *
     * @param string $paymentIntent
     * @param array $seriesItem
     *
     */
    public function updateAmount(string $paymentIntent, array $seriesItem);

    /**
     * Verify transaction status
     *
     * @param string $transactionId
     */
    public function verifyIndividualRegistration(string $transactionId): PaymentStatus;

    /**
     * Verify transaction status
     *
     * @param string $transactionId
     */
    public function verifyTeamRegistration(string $transactionId): PaymentStatus;

    /**
     * Refund Registered request
     *
     * @param string $transaction_id registration payment transaction id
     * @param int $amount registration amount
     */
    public function registrationRefund(string $transaction_id, int $amount);

    /**
     * Cancle refund request
     *
     * @param string $transaction_id registration payment transaction id
     */
    public function cancelRefund(string $transaction_id);
}
