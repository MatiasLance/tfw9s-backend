<?php

namespace App\Modules\Payment\Gateways;

use App\Models\Item;
use App\Modules\Item\Exceptions\ItemStockCannotBeLowerThanZeroException;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Mail\MailServiceInterface;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Payment\Exceptions\UnknownPaymentStatusException;
use App\Modules\Payment\PaymentGateway;
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

    /**
     * Mail Service
     * 
     * @var MailServiceInterface $mailService
     */
    protected MailServiceInterface $mailService;

    /**
     * Item Service
     * 
     * @var ItemServiceInterface $itemService
     */
    protected ItemServiceInterface $itemService;

    /**
     * Order Service
     * 
     * @var OrderServiceInterface $orderService
     */
    protected OrderServiceInterface $orderService;

    /**
     * Payment gateway code
     * 
     * @var PaymentGateway GATEWAY
     */
    public const GATEWAY = PaymentGateway::PAYPAL;

    public function __construct(MailServiceInterface $mailService, OrderServiceInterface $orderService, ItemServiceInterface $itemService, array $config = [])
    {
        $this->mailService = $mailService;
        $this->orderService = $orderService;
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
     * @todo Actual creation of order via Paypal SDK
     * @todo Decouple item model by using item service. Add find() function on ItemService
     * 
     * @see https://github.com/paypal/Checkout-PHP-SDK/blob/develop/samples/AuthorizeIntentExamples/CreateOrder.php
     */
    public function createOrder(array $items, array $metadata = [])
    {
        $total = 0;
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

        $purchaseUnits = $this->generatePurchaseUnits($lineItems, $metadata);

        foreach ($purchaseUnits as $value) {
            $total += $value['value'];
        }

        $order = $this->orderService->create(
            $metadata['transaction_id'],
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

        return $order->transaction_id;
    }

    public function verify(string $transactionId): PaymentStatus
    {
        $paypalOrder = $this->client
                                ->execute(
                                    new OrdersGetRequest($transactionId)
                                );

        $orderStatus = $this->matchStatus($paypalOrder->result->status);

        if (PaymentStatus::COMPLETE === $orderStatus) {
            $order = $this->orderService->findByTransactionId($paypalOrder->result->id);
            if (!$order->is_verified) {
                $this->orderService->markAsVerified($order->transaction_id);

                foreach ($order->items as $item) {
                    try
                    {
                        $this->itemService->decreaseStocks($item->item_id, $item->quantity, true);
                    }
                    catch(ItemStockCannotBeLowerThanZeroException $e) {
                        report($e);
                    }
                }

              $this->mailService->sendInvoice($order);
            }
        }

        return $orderStatus;
    }

    /**
     * Generate an array containing PayPal\Api\Transaction per item that the user ordered
     * 
     * @param array $items
     * 
     * @return array
     */
    protected function generatePurchaseUnits(array $items, array $metadata = []): array
    {
        $units = [];

        foreach ($items as $item) {
            $unit = [
                'currency_code' => $this->currency,
                'value' => $this->calculateItemTotal(
                    $item['item_id'], 
                    $item['quantity'],
                    $metadata['shippingChoiceCalc']
                ),
            ];

            array_push($units, $unit);
        }
        return $units;
    }

    /**
     * Calculate total item price with quantity taken into consideration
     * 
     * @param int $itemId ID of the item
     * @param int $quantity
     * 
     * @return float
     */
    protected function calculateItemTotal(int $itemId, int $quantity): float
    {
        $item = $this->itemService->retrieveItem($itemId);
        return $item->centPrice() * $quantity;
    }

    /**
     * Match payment status from Paypal to App\Modules\Payment\PaymentStatus enums
     * 
     * @param string $status Status from Paypal
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
