<?php

namespace App\Modules\Order;

use App\Models\Order;
use App\Models\ShippingOptions;
use App\Modules\Payment\PaymentGateway;

interface OrderServiceInterface
{

    /**
     * Find an existing Order by its transaction ID
     * 
     * @param String $transactionId
     * 
     * @return null|Order
     */
    public function findByTransactionId(string $transactionId): ?Order;

    /**
     * Create a new order instance
     * 
     * @param string $paymentIntentId
     * @param PaymentGateway $gateway
     * @param string $firstname
     * @param string $lastname
     * @param string $phoneNumber
     * @param string $email
     * @param string $shippingType
     * @param string|null $address Null if the shipping type is pickup
     * @param string|null $postCode Null if the shipping type is pickup
     * @param null|string $remarks
     * @param integer $total
     * @param array $items Items that was ordered
     * 
     * @return true|Order Returns true if the Order is already existing, otherwise returns the Order
     */
    public function create(
        string $paymentIntentId,
        PaymentGateway $gateway,
        string $firstname,
        string $lastname,
        string $phoneNumber,
        string $email,
        string $shippingType,
        ?string $address,
        ?string $postCode,
        ?string $remarks,
        int $shipping,
        int $total,
        array $items
    );

    /**
     * Update the shipping options.
     * 
     * Shipping options will show up on the checkout form as well on the invoice.
     * 
     * @param string|null $deliveryNote
     * @param string|null $pickupNote
     * 
     * @return bool;
     */
    public function updateShippingOptions(?string $deliveryNote, ?string $pickupNote): bool;

    /**
     * Mark order as verified
     * 
     * @param string $transactionId
     * 
     * @return bool
     */
    public function markAsVerified(string $transactionId): bool;

    /**
     * Retrieve the shipping options
     * 
     * @return ShippingOptions
     */
    public function retrieveShippingOptions(): ShippingOptions;
}
