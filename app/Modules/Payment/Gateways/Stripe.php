<?php

namespace App\Modules\Payment\Gateways;

use App\Models\Series;
use App\Models\Item;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use App\Models\DiscountCode;
use App\Modules\Item\Exceptions\ItemStockCannotBeLowerThanZeroException;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Mail\MailServiceInterface;
use App\Modules\Order\Exceptions\AddressCannotBeEmptyException;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Order\ShippingType;
use App\Modules\IndividualRegistration\IndividualRegistrationServiceInterface;
use App\Modules\TeamRegistration\TeamRegistrationServiceInterface;
use App\Modules\Payment\Exceptions\UnknownPaymentStatusException;
use App\Modules\Payment\PaymentGateway;
use App\Modules\Payment\PaymentStatus;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Models\IndividualRegistration;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Illuminate\Support\Facades\Log;
use App\Models\WaitingLounge;
use App\Jobs\SendTeamRegistrationInvoice;
use App\Models\TeamRegistration;

class Stripe extends BasePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Stripe Client
     *
     * @var \Stripe\StripeClient $stripe
     */
    protected StripeClient $stripe;

    /**
     * Mail Service
     *
     * @var MailServiceInterface $mailService
     */
    protected MailServiceInterface $mailService;

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
     * Item Service
     *
     * @var ItemServiceInterface $itemService
     */
    protected ItemServiceInterface $itemService;

    /**
     * Payment gateway code
     *
     * @var PaymentGateway GATEWAY
     */
    public const GATEWAY = PaymentGateway::STRIPE;

    public function __construct(MailServiceInterface $mailService, OrderServiceInterface $orderService, IndividualRegistrationServiceInterface $individualRegistrationService, TeamRegistrationServiceInterface $teamRegistrationService, ItemServiceInterface $itemService, array $config = [])
    {
        $this->mailService = $mailService;
        $this->orderService = $orderService;
        $this->itemService = $itemService;
        $this->individualRegistrationService = $individualRegistrationService;
        $this->teamRegistrationService = $teamRegistrationService;
        $this->stripe = new StripeClient(env('STRIPE_API_SECRET_KEY'));
        $this->liveMode = env('STRIPE_LIVE_ENVIRONMENT', env('APP_ENV') === 'production');

        parent::__construct($config);
    }

    /**
     * Create a new payment intent for custom payment flow
     *
     * @param array $items List of items and item quantity
     * @param array $metadata Metadata to associate with the Payment Intent
     * @param string $currency (Optional) If null, will use default currency
     *
     * @return string
     */
    public function createOrder($discountCode, array $items, array $metadata = [])
    {
        $lineItems = [];
        $total = 0;

        $discount = DiscountCode::where('code', $discountCode)->first();
        $hasDiscount = !empty($discount);
        $discountRate = $hasDiscount ? $discount->rate : 0;

        foreach ($items as $item) {
            $currentItem = Item::find($item['id']);
            
            if (!$currentItem) {
                continue;
            }
            
            $sizeVariantId = $item['size_variant_id'] ?? null;
            $sizeVariantPrice = null;
            
            if ($sizeVariantId && $currentItem->has_size_variants) {
                $sizeVariants = is_array($currentItem->has_size_variants) 
                    ? $currentItem->available_sizes 
                    : json_decode($currentItem->available_sizes, true);
                    
                $sizeVariant = collect($sizeVariants)
                    ->firstWhere('id', (int)$sizeVariantId);
                    
                if ($sizeVariant && isset($sizeVariant['price'])) {
                    $sizeVariantPrice = $sizeVariant['price'] * 100;
                }
            }

            $regularPrice = $sizeVariantPrice ?? $currentItem->centPrice();
            $salePrice = $currentItem->centSalePrice();
            $onSale = $currentItem->isOnSale();

            if ($onSale && $hasDiscount) {
                $price = $salePrice * (1 - $discountRate);
            } elseif ($onSale && !$hasDiscount) {
                $price = $salePrice;
            } elseif (!$onSale && $hasDiscount) {
                $price = $regularPrice * (1 - $discountRate);
            } else {
                $price = $regularPrice;
            }

            $lineItem = [
                'item_id' => $currentItem->id,
                'size_variant_id' => $sizeVariantId,
                'price' => $price,
                'quantity' => $item['quantity'],
                'selected_color' => $item['color']
            ];

            array_push($lineItems, $lineItem);
        }

        $metadata['line_items'] = json_encode($lineItems);

        $totalProduct = $this->calculateTotal($discountCode, $lineItems);

        $tax = Tax::latest()->first();
        $toggleTaxControl = ToggleTaxControl::latest()->first();

        $addTax = $tax?->getAddTaxValue();
        $gstInclusive = $toggleTaxControl?->isToggleControle2();

        $productTotal = $totalProduct['totalProduct'];
        $shippingFee = ($metadata['shipOption'] === 'delivery') ? 1000 : 0;

        if ($gstInclusive) {
            // INCLUSIVE MODE: Tax included in both products and shipping
            $totalBeforeTax = ($productTotal + $shippingFee) / (1 + ($addTax / 100));
            $taxAmount = ($productTotal + $shippingFee) - $totalBeforeTax;
            
            $productBase = $productTotal / (1 + ($addTax / 100));
            $shippingBase = $shippingFee / (1 + ($addTax / 100));
            
            $grandTotal = $productTotal + $shippingFee;

            $productValue = [
                'amount' => $grandTotal,
                'currency' => $this->currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $metadata,
            ];

            $paymentIntent = $this->stripe->paymentIntents->create($productValue);

            $responseValues = [
                'totalProduct' => $grandTotal / 100,
                'stripeToken' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id
            ];

            return response()->json($responseValues);
            
        } else {
            // EXCLUSIVE MODE: Tax added to both products AND shipping
            $taxAmount = ($productTotal + $shippingFee) * ($addTax / 100);
            $grandTotal = $productTotal + $shippingFee + $taxAmount;

            $productValue = [
                'amount' => $grandTotal,
                'currency' => $this->currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $metadata,
            ];

            $paymentIntent = $this->stripe->paymentIntents->create($productValue);

            $responseValues = [
                'totalProduct' => $grandTotal / 100,
                'stripeToken' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id
            ];

            return response()->json($responseValues);
        }
    }  

    public function verify(string $paymentIntentId): PaymentStatus
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

        if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $shippingInformation = $paymentIntent->metadata;

            $lineItems = json_decode($shippingInformation->line_items, true);

            $order = $this->orderService->create(
                                            $paymentIntent->id,
                                            self::GATEWAY,
                                            $shippingInformation->firstName,
                                            $shippingInformation->lastName,
                                            $shippingInformation->phoneNumber,
                                            $shippingInformation->email,
                                            $shippingInformation->shipOption ?? null,
                                            $shippingInformation->address ?? null,
                                            $shippingInformation->postCode ?? null,
                                            $shippingInformation->remarks,
                                            $paymentIntent->amount,
                                            $lineItems,
                                        );

            if (!$order->is_verified) {
                $this->orderService->markAsVerified($order->transaction_id);

                foreach ($lineItems as $item) {
                    try
                    {
                        $this->itemService->decreaseStocks($item['item_id'], $item['quantity'], $item['size_variant_id'], true);
                    }
                    catch(ItemStockCannotBeLowerThanZeroException $e) {
                        report($e);
                    }
                }

                $this->mailService->sendInvoice($order);
            }
        }

        return $this->matchStatus($paymentIntent->status);
    }
    

    public function createIndividualRegistration($discountcode, string $item, array $metadata = [])
    {
        $calculatedTotal = $this->calculateTotalIndividualRegistration($metadata['discountCodeId'], $item);

        $seriesItem = [
            'item_id' => $calculatedTotal['currentItem']->id,
            'price' => $calculatedTotal['regularPrice'],
            ];

        if($calculatedTotal['totalPrice'] !== 0) {
            $metadata['line_item'] = json_encode($seriesItem);

            $productValue = [
                'amount' => $calculatedTotal['totalPrice'],
                'currency' => $this->currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $metadata,
            ];

            $paymentIntent = $this->stripe->paymentIntents->create($productValue);

            $responseValues = [
                'stripeToken' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $calculatedTotal['totalPrice']
            ];

            return response()->json($responseValues);
        }else{
            $paymentId = Uuid::uuid4()->toString();
            $seriesRegistered = $this->individualRegistrationService->create(
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
                $calculatedTotal['totalPrice'],
                $seriesItem['item_id']
            );


            if (!$seriesRegistered->is_verified) {
                $this->individualRegistrationService->markAsVerified($seriesRegistered->transaction_id);
                $this->incrementMaxRegistrationIfAllowed($seriesItem['item_id']);
                $this->mailService->sendIndividualRegistrationInvoice($seriesRegistered);
            }

            return response()->json([
                'amount' => $calculatedTotal['totalPrice'],
                'transactionId' => $seriesRegistered->transaction_id
            ]);
        }
    }

    // public function createTeamRegistration($discountcode, string $item, array $metadata = [])
    // {
    //     $calculatedTotal = $this->calculateTotalTeamRegistration($item);

    //     $seriesItem = [
    //         'item_id' => $calculatedTotal['currentItem']->id,
    //         'price' => $calculatedTotal['regularPrice'],
    //     ];

    //     $metadata['line_item'] = json_encode($seriesItem);

    //     $productValue = [
    //         'amount' => $calculatedTotal['totalPrice'],
    //         'currency' => $this->currency,
    //         'automatic_payment_methods' => [
    //             'enabled' => true,
    //         ],
    //         'metadata' => $metadata,
    //     ];

    //     $paymentIntent = $this->stripe->paymentIntents->create($productValue);

    //     $responseValues = [
    //         'stripeToken' => $paymentIntent->client_secret,
    //         'paymentIntentId' => $paymentIntent->id
    //     ];

    //     return response()->json($responseValues);
    // }

    public function createTeamRegistration($discountcode, string $item, array $metadata = [], ?string $clientToken)
    {
        if (empty($item)) {
            throw new InvalidArgumentException('Item identifier cannot be empty');
        }

        try {
            $calculatedTotal = $this->calculateTotalTeamRegistration($item);

            if (!isset($calculatedTotal['currentItem'], $calculatedTotal['regularPrice'], $calculatedTotal['totalPrice'])) {
               throw new RuntimeException('Invalid calculation response structure');
            }

            $lineItemData = [
                'item_id' => $calculatedTotal['currentItem']->id,
                'price' => $calculatedTotal['regularPrice'],
            ];

            $paymentMetadata = array_merge($metadata, [
              'line_item' => json_encode($lineItemData),
              'client_token' => $clientToken
            ]);

            $productValue = [
                'amount' => $calculatedTotal['totalPrice'],
                'currency' => $this->currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $paymentMetadata,
            ];

            $paymentIntent = $this->stripe->paymentIntents->create($productValue);

            $responseValues = [
                'stripeToken' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id
            ];

            return response()->json($responseValues);

        } catch(ApiErrorException $e) {
            throw new TeamRegistrationException(
                'Payment service temporarily unavailable. Please try again.',
                previous: $e
            );
        }
    }

    public function verifyIndividualRegistration(string $paymentIntentId): PaymentStatus
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);
        
        if (!$paymentIntent) {
            throw new RuntimeException('Payment intent not found');
        }

        if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $registrationInformation = $paymentIntent->metadata;

            try {
                $lineItem = json_decode($registrationInformation->line_item, true);
                
                if (!isset($lineItem['item_id'])) {
                    throw new RuntimeException('Invalid line item structure');
                }

                $seriesRegistered = $this->individualRegistrationService->create(
                    $paymentIntent->id,
                    self::GATEWAY,
                    $registrationInformation->contactFirstName,
                    $registrationInformation->contactLastName,
                    $registrationInformation->contactPhoneNumber,
                    $registrationInformation->contactEmail,
                    $registrationInformation->playerFirstName,
                    $registrationInformation->playerLastName,
                    $registrationInformation->dob,
                    $registrationInformation->teamName,
                    $registrationInformation->ageGroup,
                    $paymentIntent->amount,
                    $lineItem['item_id'],
                );

                if (!$seriesRegistered->is_verified) {
                    $this->individualRegistrationService->markAsVerified($seriesRegistered->transaction_id);
                    $this->incrementMaxRegistrationIfAllowed($lineItem['item_id']);
                    $this->mailService->sendIndividualRegistrationInvoice($seriesRegistered);
                }
            } catch (Exception $e) {
                Log::error($e);
                report($e);
            }
        }

        return $this->matchStatus($paymentIntent->status);
    }

    public function verifyTeamRegistration(string $paymentIntentId): PaymentStatus
    {
        $existingRegistration = TeamRegistration::where('transaction_id', $paymentIntentId)->first();
        if ($existingRegistration && $existingRegistration->is_verified) {
            return $this->matchStatus('succeeded');
        }

        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);
        
        if (!$paymentIntent) {
            throw new RuntimeException('Payment intent not found');
        }

        if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $registrationInformation = $paymentIntent->metadata;

            try {
                $lineItem = json_decode($registrationInformation->line_item, true);
                
                if (!isset($lineItem['item_id'])) {
                    throw new RuntimeException('Invalid line item structure');
                }

                $seriesRegistered = $this->teamRegistrationService->create(
                    $paymentIntent->id,
                    self::GATEWAY,
                    $registrationInformation->coachesEmail,
                    $registrationInformation->coachesName,
                    $registrationInformation->coachesPhoneNumber,
                    $registrationInformation->managerEmail,
                    $registrationInformation->managerName,
                    $registrationInformation->managerPhoneNumber,
                    $registrationInformation->teamName,
                    $registrationInformation->ageGroup,
                    $registrationInformation->pool,
                    $paymentIntent->amount,
                    $lineItem['item_id'],
                );

                if (!$seriesRegistered->is_verified) {
                    $this->teamRegistrationService->markAsVerified($seriesRegistered->transaction_id);
                    $this->incrementMaxRegistrationIfAllowed($lineItem['item_id']);
                    // $this->mailService->sendTeamRegistrationInvoice($seriesRegistered);
                    SendTeamRegistrationInvoice::dispatch($seriesRegistered);

                    // Clean up lounge now that they are officially "checking out"
                    WaitingLounge::where('client_id', $registrationInformation->client_token)->delete();
                }

            } catch (Exception $e) {
                Log::error($e);
                report($e);
            }
        }

        return $this->matchStatus($paymentIntent->status);
    }

    /**
     * Retrieve a payment intent
     *
     * @param string $paymentIntentId
     *
     * @return PaymentIntent
     */
    protected function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Calculate the total price
     *
     * @param array $items
     *
     * @return int
     */
    protected function calculateTotal($discountcode, array $items): array
    {
        $total = 0;
        $res = DiscountCode::where('code', $discountcode)->first();
        $hasDiscount = !empty($discountcode);

        foreach ($items as $index => $item) {
            $currentItem = Item::find($item['item_id']);
            $sizeVariantId = $item['size_variant_id'] ?? null;
            
            $price = $currentItem->calculateFinalPrice($sizeVariantId, $hasDiscount, $res->rate ?? 0);
            
            $subtotal = (float)($price * (int)$item['quantity']);
            
            $total += $subtotal;
        }
        
        return ['totalProduct' => $total];
    }


    /**
     * Calculate total item price with quantity taken into consideration
     *
     * @param int $item ID of the item
     *
     * @return array
     */
    protected function calculateTotalIndividualRegistration($discountCodeID, int $item): array
    {
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Series::find($item);
        $regularPrice = $currentItem->centPrice();

        $taxAmount = 0;

        if($discountCodeID !== 0){
            $discountCode = DiscountCode::where('id', $discountCodeID)->first();
            $discountRate = floatval($discountCode->rate);

            if (!$isInclusive && $discountRate != 0.0) {
                $taxRate = $addTax / 100;
                $price = $regularPrice * (1 - $discountRate);
                $taxAmount = $regularPrice * $taxRate;
                $totalPrice = intval($price + $taxAmount);
                $isInclusive = false;
            } elseif ($isInclusive && $discountRate != 0.0) {
                $price = $regularPrice * (1 - $discountRate);
                $totalPrice = intval($price);
                $isInclusive = true;
            } elseif (!$isInclusive && $discountRate === 0.0) {
                $taxRate = $addTax / 100;
                $taxAmount = $regularPrice * $taxRate;
                $totalPrice = intval($regularPrice + $taxAmount);
                $isInclusive = false;
            } else {
                $totalPrice = intval($regularPrice);
                $isInclusive = true;
            }

            return [
                'currentItem' => $currentItem,
                'regularPrice' => $regularPrice,
                'totalPrice' => $totalPrice
            ];
        }else{
            if (!$isInclusive) {
                $taxRate = $addTax / 100;
                $price = $regularPrice;
                $taxAmount = $regularPrice * $taxRate;
                $totalPrice = intval($price + $taxAmount);
                $isInclusive = false;
            } elseif ($isInclusive) {
                $totalPrice = intval($regularPrice);
                $isInclusive = true;
            } elseif (!$isInclusive) {
                $taxRate = $addTax / 100;
                $taxAmount = $regularPrice * $taxRate;
                $totalPrice = intval($regularPrice + $taxAmount);
                $isInclusive = false;
            } else {
                $totalPrice = intval($regularPrice);
                $isInclusive = true;
            }

            return [
                'currentItem' => $currentItem,
                'regularPrice' => $regularPrice,
                'totalPrice' => $totalPrice
            ];
        }
    }

    protected function calculateTotalTeamRegistration(int $item): array
    {
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Series::find($item);
        $regularPrice = $currentItem->centPrice();

        $taxAmount = 0;

        if (!$isInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = false;
        } elseif ($isInclusive) {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = true;
        } elseif (!$isInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = false;
        } else {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = true;
        }

        return [
            'currentItem' => $currentItem,
            'regularPrice' => $regularPrice,
            'totalPrice' => $totalPrice
        ];
    }

    /**
     * Match status enum from Stripe to App\Modules\Payment\PaymentStatus enums
     *
     * @param string $status Status from Stripe
     *
     * @return PaymentStatus
     */
    protected function matchStatus(string $status): PaymentStatus
    {
        switch ($status) {
            case 'requires_payment_method':
                return PaymentStatus::PENDING;

            case 'requires_confirmation':
                return PaymentStatus::PENDING;

            case 'requires_action':
                return PaymentStatus::PENDING;

            case 'requires_capture':
                return PaymentStatus::PENDING;

            case 'processing':
                return PaymentStatus::PROCESSING;

            case 'canceled':
                return PaymentStatus::CANCELLED;

            case 'succeeded':
                return PaymentStatus::COMPLETE;

            default:
                throw new UnknownPaymentStatusException('Stripe returned an unknown payment status');
                break;
        }
    }
    
    public function updateAmount(string $paymentIntentId, array $updateParams): bool
    {
        try {
            $this->stripe->paymentIntents->update($paymentIntentId, $updateParams);
            return true;
        } catch (ApiErrorException $e) {
            Log::error($e);
            report($e);
            return false;
        }
    }

    public function registrationRefund(string $transaction_id, int $amount): ?string
    {
        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $transaction_id,
                'amount' => $amount,
            ]);
            return $refund->id;
        } catch (ApiErrorException $e) {
            Log::error($e);
            return null;
        }
    }

    public function cancelRefund(string $refund_id): ?string
    {
        try {
            $refund = $this->stripe->refunds->cancel($refund_id, []);
            return $refund->id;
        } catch (ApiErrorException $e) {
            Log::error($e);
            return null;
        }
    }
    
    protected function incrementMaxRegistrationIfAllowed(int $seriesId): void
    {
        try {
            $series = Series::with('ageGroup')->findOrFail($seriesId);
        
            if ($series->type !== 'weekly' || !$series->ageGroup) {
                return;
            }
        
            $maxAge = $series->ageGroup->max_age;
            $cap = ($maxAge <= 9) ? 12 : 15;
        
            if ($series->max_registration < $cap) {
                $series->increment('max_registration');
            }
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            report($e);
        } catch (Exception $e) {
            Log::error($e);
            report($e);
        }
    }
    
    protected function hasDecimal($value)
    {
        // https://www.php.net/manual/en/function.fmod.php
        return fmod((float)$value, 1) !== 0.0;
    }
}
