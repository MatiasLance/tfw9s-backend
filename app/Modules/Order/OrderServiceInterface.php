<?php

namespace App\Modules\Order;

use App\Models\Order;
use App\Models\ShippingOptions;

interface OrderServiceInterface
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
     * Create a new order instance
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
     * Retrieve the shipping options
     * 
     * @return ShippingOptions
     */
    public function retrieveShippingOptions(): ShippingOptions;

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
}
