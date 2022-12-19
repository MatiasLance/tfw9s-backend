<?php

namespace App\Modules\Payment\Gateways;

use App\Modules\Item\ItemServiceInterface;
use App\Modules\Payment\Exceptions\UnknownPaymentStatusException;
use App\Modules\Payment\PaymentStatus;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

class Paypal extends BasePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Api Context needed for Paypal Auth
     * 
     * @var PayPalHttpClient $client
     */
    protected PayPalHttpClient $client;

    public ItemServiceInterface $itemService;

    public function __construct(ItemServiceInterface $itemService, array $config = [])
    {
        $this->itemService = $itemService;

        if (env('PAYPAL_ENVIRONMENT' === 'production', env('APP_ENV') === 'production')) {
            $clientId = env('PAYPAL_LIVE_CLIENT_ID');
            $secretKey = env('PAYPAL_LIVE_SECRET_KEY');

            $environment = new ProductionEnvironment($clientId, $secretKey);
        } else {
            $clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
            $secretKey = env('PAYPAL_SANDBOX_SECRET_KEY');

            $environment = new SandboxEnvironment($clientId, $secretKey);
        }

        $this->client = new PayPalHttpClient($environment);

        parent::__construct($config);
    }

    /**
     * @todo
     * @see https://github.com/paypal/Checkout-PHP-SDK/blob/develop/samples/AuthorizeIntentExamples/CreateOrder.php
     */
    public function createOrder(array $items, array $metadata = [])
    {
        $purchaseUnits = $this->generatePurchaseUnits($items);
        //
    }

    public function verify(string $transactionId): PaymentStatus
    {
        $orderStatus = $this->client
                                ->execute(
                                    new OrdersGetRequest($transactionId)
                                );

        return $this->matchStatus($orderStatus->result->status);
    }

    /**
     * Generate an array containing PayPal\Api\Transaction per item that the user ordered
     * 
     * @param array $items
     * 
     * @return array
     */
    protected function generatePurchaseUnits(array $items): array
    {
        $units = [];

        foreach ($items as $item) {
            $unit = [
                'currency_code' => $this->currency,
                'value' => $this->calculateItemTotal($item['item_id'], $item['quantity']),
            ];

            array_push($units, $unit);
        }
        return $units;
    }

    protected function calculateItemTotal(int $itemId, int $quantity): float
    {
        $item = $this->itemService->retrieveItem($itemId);
        return $item->centPrice() + $quantity;
    }

    /**
     * Match payment status from Paypal to App\Modules\Payment\PaymentStatus enums
     * 
     * @param string $status Status from Stripe
     * 
     * @return PaymentStatus
     */
    protected function matchStatus(string $status): PaymentStatus
    {
        switch ($status) {
            case 'PAYER_ACTION_REQUIRED':
                return PaymentStatus::PENDING;

            case 'CREATED':
                return PaymentStatus::PENDING;

            case 'APPROVED':
                return PaymentStatus::PENDING;

            case 'SAVED':
                return PaymentStatus::PROCESSING;

            case 'VOIDED':
                return PaymentStatus::FAILED;

            case 'COMPLETED':
                return PaymentStatus::COMPLETE;

            default:
                throw new UnknownPaymentStatusException('Paypal returned an unknown payment status');
                break;
        }
    }
}
