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
    public function createOrder(array $items, array $metadata = []);

    /**
     * Verify transaction status
     * 
     * @param string $transactionId
     */
    public function verify(string $transactionId): PaymentStatus;
}