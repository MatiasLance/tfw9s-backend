<?php

namespace App\Modules\Payment\Gateways;

use App\Models\Item;
use App\Modules\Payment\Exceptions\PaymentFailedException;
use App\Modules\Payment\Gateways\Square;
use App\Modules\Payment\PaymentGateway;
use Ramsey\Uuid\Uuid;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;

/**
 * Extends square since the afterpay integration is using Square as payment gateway
 */
class Afterpay extends Square
{
    /**
     * Payment gateway code
     * 
     * @var PaymentGateway GATEWAY
     */
    public const GATEWAY = PaymentGateway::AFTERPAY;

    /**
     * Need to override Square implementation so that the gateway override applies
     */
    public function createOrder(array $items, array $metadata = [])
    {
        $lineItems = [];

        foreach ($items as $item) {
            $currentItem = Item::find($item['id']);

            $lineItem = [
                'item_id' => $currentItem->id,
                'price' => $currentItem->centPrice(),
                'quantity' => $item['quantity'],
            ];
            array_push($lineItems, $lineItem);
        }
        $total = $this->calculateTotal($lineItems);
        
        $money = new Money();
        $money->setAmount($total);
        $money->setCurrency(strtoupper($this->currency));

        $createPaymentRequest = new CreatePaymentRequest($metadata['card_token'], Uuid::uuid4(), $money);
        $paymentsApi = $this->client->getPaymentsApi();
        $response = $paymentsApi->createPayment($createPaymentRequest);

        if ($response->isSuccess()) {
            $paymentId = $response->getResult()->getPayment()->getId();

            $this->orderService->create(
                $paymentId,
                self::GATEWAY,
                $metadata['firstName'],
                $metadata['lastName'],
                $metadata['phoneNumber'],
                $metadata['email'],
                $metadata['shippingType'],
                $metadata['address'],
                $metadata['postCode'],
                $metadata['remarks'] ?? '',
                $total,
                $lineItems
            );

            return $paymentId;
        } else {
            throw new PaymentFailedException('Transaction failed');
        }
    }
}