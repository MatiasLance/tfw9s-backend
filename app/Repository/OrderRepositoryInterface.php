<?php

namespace App\Repository;

use App\Models\Order;
use App\Models\ShippingOptions;

interface OrderRepositoryInterface
{

    /**
     * Find an existing order by its transaction id
     * 
     * @param string $transactionId
     * 
     * @return Order
     */
    public function findByTransactionId(string $transactionId): Order;

    /**
     * Create a new Order
     * 
     * @param string $paymentIntentId
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
        string $firstname,
        string $lastname,
        string $phoneNumber,
        string $email,
        string $shippingType,
        ?string $address,
        ?string $postCode,
        ?string $remarks,
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
     * @return bool
     */
    public function updateShippingOptions(?string $deliveryNote, ?string $pickupNote): bool;

    /**
     * Retrieve the shipping options
     * 
     * @return ShippingOptions
     */
    public function retrieveShippingOptions(): ShippingOptions;
}