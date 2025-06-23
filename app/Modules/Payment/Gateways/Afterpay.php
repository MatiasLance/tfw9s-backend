<?php

namespace App\Modules\Payment\Gateways;

use App\Models\Series;
use App\Models\Item;
use App\Models\DiscountCode;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use App\Modules\IndividualRegistration\IndividualRegistrationServiceInterface;
use App\Modules\TeamRegistration\TeamRegistrationServiceInterface;
use App\Modules\Item\Exceptions\ItemStockCannotBeLowerThanZeroException;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Mail\MailServiceInterface;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Payment\Exceptions\PaymentFailedException;
use App\Modules\Payment\Gateways\Square;
use App\Modules\Payment\PaymentGateway;
use App\Modules\Payment\PaymentStatus;
use Ramsey\Uuid\Uuid;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;
use Square\SquareClient;
use Square\Models\RefundPaymentRequest as SquareRefundRequest;

/**
 * Extends square since the afterpay integration is using Square as payment gateway
 */
class Afterpay extends BasePaymentGateway implements PaymentGatewayInterface
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
     * Individual Registration Service
     *
     * @var IndividualRegistrationServiceInterface $individualRegistrationService
     */
    protected IndividualRegistrationServiceInterface $individualRegistrationService;

    /**
     * Team Registration Service
     *
     * @var TeamRegistrationServiceInterface $teamRegistrationService
     */
    protected TeamRegistrationServiceInterface $teamRegistrationService;

    /**
     * Payment gateway code
     * 
     * @var PaymentGateway GATEWAY
     */
    public const GATEWAY = PaymentGateway::AFTERPAY;

    public function __construct(MailServiceInterface $mailService, OrderServiceInterface $orderService, ItemServiceInterface $itemService, IndividualRegistrationServiceInterface $individualRegistrationService, TeamRegistrationServiceInterface $teamRegistrationService, array $config = [])
    {
        $this->mailService = $mailService;
        $this->orderService = $orderService;
        $this->itemService = $itemService;
        $this->individualRegistrationService = $individualRegistrationService;
        $this->teamRegistrationService = $teamRegistrationService;
        $this->client = new SquareClient([
            'accessToken' => env('SQUARE_ACCESS_TOKEN'),
            'environment' => $this->retrieveClientEnvironment()
        ]);
        $this->locationId = env('SQUARE_LOCATION_ID');

        parent::__construct($config);
    }

    /**
     * Need to override Square implementation so that the gateway override applies
     */
    public function createOrder($discountcode, array $items, array $metadata = [])
    {
        $lineItems = [];

        foreach ($items as $item) {
            $currentItem = Item::find($item['id']);
            $onSale = $currentItem->isOnSale();
            $hasDiscount = !empty($res);
            $salePrice = $currentItem->centSalePrice();
            $regularPrice = $currentItem->centPrice();

            if ($onSale && $hasDiscount) {
                $price = $salePrice * (1 - $res->rate);
            } elseif ($onSale && !$hasDiscount) {
                $price = $salePrice;
            } elseif (!$onSale && $hasDiscount) {
                $price = $regularPrice * (1 - $res->rate);
            } else {
                $price = $regularPrice;
            }
            $lineItem = [
                'item_id' => $currentItem->id,
                'price' => $currentItem->centPrice(),
                'quantity' => $item['quantity'],
            ];
            array_push($lineItems, $lineItem);
        }

        $metadata['line_item'] = json_encode($lineItems);

        $totalshipping = $this->calculateTotal($discountcode, $lineItems);

        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        $taxAmount = 0;
        $totalPrice = 0;

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        if (!$isInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $totalshipping['totalProduct'] * $taxRate;
            $totalPrice = intval($totalshipping['totalProduct'] + $taxAmount);
            $isInclusive = false;
        } elseif ($isInclusive) {
            $taxRate = $includeTax / 100;
            $taxAmount = $totalshipping['totalProduct'] * $taxRate;
            $totalPrice = intval($totalshipping['totalProduct'] );
            $isInclusive = true;
        } else {
            $totalPrice = intval($totalshipping['totalProduct']);
            $isInclusive = true;
        }

        $total = $totalPrice;

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

    public function createIndividualRegistration($discountcode, string $item, array $metadata = [])
    {
        $calculatedTotal = $this->calculateTotalRegistration($discountcode, $item);

        $total = $calculatedTotal['totalPrice'] / 100;

        $metadata['total_price'] = $total * 100;
        $metadata['item_id'] = $calculatedTotal['currentItem']->id;

        $money = new Money();
        $money->setAmount((int) round($total * 100));
        $money->setCurrency(strtoupper($this->currency));

        $createPaymentRequest = new CreatePaymentRequest($metadata['card_token'], Uuid::uuid4(), $money);
        $paymentsApi = $this->client->getPaymentsApi();
        $response = $paymentsApi->createPayment($createPaymentRequest);

        if ($response->isSuccess()) {
            $this->incrementMaxRegistrationIfAllowed($calculatedTotal['currentItem']->id);
            $paymentId = $response->getResult()->getPayment()->getId();

            $this->individualRegistrationService->create(
                $paymentId,
                self::GATEWAY,
                $metadata['contactFirstName'],
                $metadata['contactLastName'],
                $metadata['contactPhoneNumber'],
                $metadata['contactEmail'],
                $metadata['playerFirstName'],
                $metadata['playerLastName'],
                $metadata['dob'],
                $metadata['teamName'],
                $metadata['ageGroup'],
                $metadata['total_price'],
                $metadata['item_id']
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

    public function verifyIndividualRegistration(string $paymentIntentId): PaymentStatus
    {
        $paymentsApi = $this->client->getPaymentsApi();
        $response = $paymentsApi->getPayment($paymentIntentId);

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
            $seriesRegistered = $this->individualRegistrationService->findByTransactionId(
                                                $response
                                                    ->getResult()
                                                    ->getPayment()
                                                    ->getId()
                                            );
            if (!$seriesRegistered->is_verified) {
                $this->individualRegistrationService->markAsVerified($seriesRegistered->transaction_id);
                $this->mailService->sendIndividualRegistrationInvoice($seriesRegistered);
            }
        }

        return $orderStatus;
    }

    public function verifyTeamRegistration(string $paymentIntentId): PaymentStatus
    {
        $paymentsApi = $this->client->getPaymentsApi();
        $response = $paymentsApi->getPayment($paymentIntentId);

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
            $seriesRegistered = $this->teamRegistrationService->findByTransactionId(
                                                $response
                                                    ->getResult()
                                                    ->getPayment()
                                                    ->getId()
                                            );
            if (!$seriesRegistered->is_verified) {
                $this->teamRegistrationService->markAsVerified($seriesRegistered->transaction_id);
                $this->mailService->sendTeamRegistrationInvoice($seriesRegistered);
            }
        }

        return $orderStatus;
    }

    public function cancelRefund(string $refund_id): ?string
    {
        return response()->json(['message' => 'No cancellation refund.']);
    }

    public function updateAmount(string $paymentIntent, array $updateParams)
    {
        try {
            $money = new Money();
            $money->setAmount((int) round($updateParams['price'] * 100));
            $money->setCurrency($currency);

            $body = new UpdatePaymentRequest($money);
            $body->setIdempotencyKey(uniqid());

            $apiResponse = $this->client->getPayments()->updatePayment($paymentIntent, $body);

            if ($apiResponse->isSuccess()) {
                return true;
            } else {
                return response()->json([
                    'success' => false,
                    'errors' => $apiResponse->getErrors()
                ], 400);
            }
        } catch (ApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getErrors()
            ], 500);
        }
    }

    public function registrationRefund(string $transaction_id, int $amount): ?string
    {
        $paymentsApi = $this->client->getRefundsApi();

        $money = new Money();
        $money->setAmount($amount * 100);
        $money->setCurrency(strtoupper($this->currency));

        $refundRequest = new SquareRefundRequest(
            $transaction_id,
            $money
        );

        try {
            $response = $paymentsApi->refundPayment($this->locationId, $refundRequest);

            if ($response->isSuccess()) {
                return response()->json([
                    'success' => true,
                    'refund' => $response->getResult(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $response->getErrors(),
                ], 500);
            }
        } catch (ApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getErrors(),
            ], 500);
        }

    }

    /**
     * Calculate the total amount to be paid
     * 
     * @param array $items
     * 
     * @return float
     */
    protected function calculateTotal($discountcode, array $items): array
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
        }

        return ['totalProduct' => $total];
    }

    /**
     * Calculate total item price with quantity taken into consideration
     * 
     * @param int $itemId ID of the item
     * @param int $quantity
     * 
     * @return float
     */
    protected function calculateItemTotal($discountcode, int $item): float
    {
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        $res = DiscountCode::where('code', $discountcode)->first();
        $hasDiscount = !empty($res);

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Item::find($item);
        $regularPrice = $currentItem->centPrice();
        $hasDiscount = !empty($discountcode);
        $taxAmount = 0;

        if (!$isInclusive && $hasDiscount) {
            $taxRate = $addTax / 100;
            $price = $regularPrice * (1 - $res->rate);
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($price + $taxAmount);
            $isInclusive = false;
        } elseif ($isInclusive && $hasDiscount) {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $price = $regularPrice * (1 - $res->rate);
            $totalPrice = intval($price);
            $isInclusive = true;
        } elseif (!$isInclusive && !$hasDiscount) {
            $taxRate = $addTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = false;
        } else {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice);
            $isInclusive = true;
        }

        return [
            'currentItem' => $currentItem,
            'regularPrice' => $regularPrice,
            'totalPrice' => $totalPrice
        ];
    }

        /**
     * Calculate total item price with quantity taken into consideration
     *
     * @param int $item ID of the item
     *
     * @return array
     */
    protected function calculateTotalRegistration($discountcode, int $item): array
    {
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        $res = DiscountCode::where('code', $discountcode)->first();
        $hasDiscount = !empty($res);

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Series::find($item);
        $regularPrice = $currentItem->centPrice();
        $hasDiscount = !empty($discountcode);
        $taxAmount = 0;

        if (!$isInclusive && $hasDiscount) {
            $taxRate = $addTax / 100;
            $price = $regularPrice * (1 - $res->rate);
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($price + $taxAmount);
            $isInclusive = false;
        } elseif ($isInclusive && $hasDiscount) {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $price = $regularPrice * (1 - $res->rate);
            $totalPrice = intval($price);
            $isInclusive = true;
        } elseif (!$isInclusive && !$hasDiscount) {
            $taxRate = $addTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = false;
        } else {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice);
            $isInclusive = true;
        }

        return [
            'currentItem' => $currentItem,
            'regularPrice' => $regularPrice,
            'totalPrice' => $totalPrice
        ];
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

    protected function incrementMaxRegistrationIfAllowed(int $seriesId): void
    {
        $series = Series::with('ageGroup')->findOrFail($seriesId);
    
        if ($series->type !== 'weekly' || !$series->ageGroup) {
            return;
        }
    
        $maxAge = $series->ageGroup->max_age;
        $cap = ($maxAge <= 9) ? 12 : 15;
    
        if ($series->max_registration < $cap) {
            $series->increment('max_registration');
        }
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