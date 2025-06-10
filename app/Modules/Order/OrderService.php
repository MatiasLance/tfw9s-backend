<?php

namespace App\Modules\Order;

use App\Models\Order;
use App\Models\ShippingOptions;
use App\Modules\Payment\PaymentGateway;
use App\Repository\OrderRepositoryInterface;

class OrderService implements OrderServiceInterface
{

    /**
     * Order Repository
     * 
     * @var OrderRepositoryInterface $orderRepository
     */
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    
    public function findByTransactionId(string $transactionId): ?Order
    {
        return $this->orderRepository->findByTransactionId($transactionId);
    }

    public function create(string $paymentIntentId, PaymentGateway $gateway, string $firstname, string $lastname, string $phoneNumber, string $email, ?string $address, ?string $postCode, ?string $remarks, int $total, array $items)
    {
        return $this->orderRepository->create(
            $paymentIntentId,
            $gateway,
            $firstname,
            $lastname,
            $phoneNumber,
            $email,
            $address,
            $postCode,
            $remarks,
            $total,
            $items
        );
    }

    public function updateShippingOptions(?string $deliveryNote, ?string $pickupNote): bool
    {
        return $this->orderRepository->updateShippingOptions($deliveryNote, $pickupNote);
    }

    public function markAsVerified(string $transactionId): bool
    {
        return $this->orderRepository->markAsVerified($transactionId);
    }

    /**
     * Retrieve the shipping options
     * 
     * @return ShippingOptions
     */
    public function retrieveShippingOptions(): ShippingOptions
    {
        return $this->orderRepository->retrieveShippingOptions();
    }
}