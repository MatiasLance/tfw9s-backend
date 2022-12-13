<?php

namespace App\Modules\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a new order request
     */
    public function createOrder(array $items, array $metadata = []);
}