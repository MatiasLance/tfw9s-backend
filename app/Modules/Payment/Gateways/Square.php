<?php

namespace App\Modules\Payment\Gateways;

use App\Models\Item;
use App\Modules\Item\Exceptions\ItemStockCannotBeLowerThanZeroException;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Mail\MailServiceInterface;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Payment\Exceptions\PaymentFailedException;
use App\Modules\Payment\Exceptions\UnknownPaymentStatusException;
use App\Modules\Payment\PaymentGateway;
use App\Modules\Payment\PaymentStatus;
use Ramsey\Uuid\Uuid;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;
use Square\SquareClient;

class Square extends BasePaymentGateway implements PaymentGatewayInterface
{
    /**
     * The Square Client class provided by Square SDK
     * 
     * @var SquareClient $client
     */
    protected SquareClient $client;

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
    public const GATEWAY = PaymentGateway::SQUARE;

    public function __construct(MailServiceInterface $mailService, OrderServiceInterface $orderService, ItemServiceInterface $itemService, array $config = [])
    {
        $this->mailService = $mailService;
        $this->orderService = $orderService;
        $this->itemService = $itemService;

        $this->client = new SquareClient([
            'accessToken' => env('SQUARE_ACCESS_TOKEN'),
            'environment' => $this->retrieveClientEnvironment()
        ]);

        parent::__construct($config);
    }

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

    public function verify(string $transactionId): PaymentStatus
    {
        $paymentsApi = $this->client->getPaymentsApi();
        $response = $paymentsApi->getPayment($transactionId);

        if ($response->isSuccess()) {
            $orderStatus = $this->matchStatus(
                $response
                    ->getResult()
                    ->getPayment()
                    ->getStatus()
            );
        } else {
            return PaymentStatus::FAILED;
        }

        if (PaymentStatus::COMPLETE === $orderStatus) {
            $order = $this->orderService->findByTransactionId(
                                                $response
                                                    ->getResult()
                                                    ->getPayment()
                                                    ->getId()
                                            );
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
     * Calculate the total amount to be paid
     * 
     * @param array $items
     * 
     * @return float
     */
    protected function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
        }

        return $total;
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
     * Retrieve the Square client environment
     * 
     * @return string
     */
    protected function retrieveClientEnvironment(): string
    {
        return env(
            'SQUARE_ENVIRONMENT', 
            env('APP_ENV') === 'production' ? 'production' : 'sandbox'
        );
    }

    /**
     * Match payment status from Square to App\Modules\Payment\PaymentStatus enums
     * 
     * @param string $status Status from Square
     * 
     * @return PaymentStatus
     */
    protected function matchStatus(string $status): PaymentStatus
    {
        switch ($status) {

            case 'PENDING':
                return PaymentStatus::PENDING;

            case 'APPROVED':
                return PaymentStatus::PROCESSING;

            case 'CANCELLED':
                return PaymentStatus::CANCELLED;

            case 'FAILED':
                return PaymentStatus::FAILED;

            case 'COMPLETED':
                return PaymentStatus::COMPLETE;

            default:
                throw new UnknownPaymentStatusException('Square returned an unknown payment status');
                break;
        }
    }
}