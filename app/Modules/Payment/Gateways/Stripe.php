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
use App\Jobs\SendOrderInvoice;
use App\Models\TeamRegistration;
use App\Models\RegistrationPaymentAttempt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        $shippings = [];
        $total = 0;
        $shippingFee = 0;

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
                $price = (int) round($salePrice * (1 - $discountRate));
            } elseif ($onSale && !$hasDiscount) {
                $price = $salePrice;
            } elseif (!$onSale && $hasDiscount) {
                $price = (int) round($regularPrice * (1 - $discountRate));
            } else {
                $price = $regularPrice;
            }

            $lineItem = [
                'item_id' => $currentItem->id,
                'size_variant_id' => $sizeVariantId,
                'price' => $price,
                'quantity' => $item['quantity'],
                'selected_color' => $item['color'] ?? null
            ];

            $shipping = [
                'has_shipping' => $currentItem->has_shipping,
                'shipping_charge' => $currentItem->shipping_charge
            ];

            array_push($lineItems, $lineItem);
            array_push($shippings, $shipping);
        }

        $cartToken = Str::random(16);
        $cartPayload = [
            'line_items' => $lineItems,
            'discount_code' => $discountCode,
            'grand_total' => null,
            'metadata' => $metadata,
            'created_at' => now()->timestamp,
        ];
        

        Cache::put(
            "cart_pending:{$cartToken}",
            $cartPayload,
            now()->addMinutes(15)
        );

        $totalProduct = $this->calculateTotal($discountCode, $lineItems);

        $tax = Tax::latest()->first();
        $toggleTaxControl = ToggleTaxControl::latest()->first();

        $addTax = $tax?->getAddTaxValue();
        $gstInclusive = $toggleTaxControl?->isToggleControle2();

        $productTotal = $totalProduct['totalProduct'] * 100;

        $items = collect($shippings);
        
        $hasUnshippable = $items->contains('has_shipping', false);

        if (!$hasUnshippable && ($metadata['shipOption'] === 'delivery')) {
            $firstShippable = $items->firstWhere('has_shipping', true);
            $shippingFee = $firstShippable['shipping_charge'] * 100 ?? 0;
            // $shippingFee = $items->sum('shipping_charge') * 100;
        } else {
            $shippingFee = 0;
        }

        if ($gstInclusive) {
            $totalBeforeTax = ($productTotal + floatval($shippingFee)) / (1 + ($addTax / 100));
            $taxAmount = ($productTotal + floatval($shippingFee)) - $totalBeforeTax;
            $grandTotal = $productTotal + floatval($shippingFee);
        } else {
            $taxAmount = (int) round(($productTotal + floatval($shippingFee)) * ($addTax / 100));
            $grandTotal = $productTotal + floatval($shippingFee) + $taxAmount;
        }

        $cartPayload['grand_total'] = $grandTotal;
        Cache::put("cart_pending:{$cartToken}", $cartPayload, now()->addMinutes(15));

        $stripeMetadata = array_merge(
            array_filter($metadata, function($v) {
                return is_string($v) || is_numeric($v) || is_bool($v);
            }),
            [
                'cart_token' => $cartToken,
                'ship_option' => $metadata['shipOption'] ?? 'pickup',
            ]
        );

        $productValue = [
            'amount' => $grandTotal,
            'currency' => $this->currency,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => $stripeMetadata,
        ];

        $paymentIntent = $this->stripe->paymentIntents->create($productValue);

        return response()->json([
            'totalProduct' => $grandTotal / 100,
            'stripeToken' => $paymentIntent->client_secret,
            'paymentIntentId' => $paymentIntent->id
        ]);
    }

    public function verify(string $paymentIntentId): PaymentStatus
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

        if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $meta = $paymentIntent->metadata;
            $cartToken = $meta->cart_token ?? null;
            
            $cachedCart = $cartToken ? Cache::get("cart_pending:{$cartToken}") : null;
            
            if (!$cachedCart) {
                report(new Exception("Cart cache miss for token: {$cartToken}"));
                return $this->matchStatus('failed');
            }

            if ((int) $paymentIntent->amount !== (int) ($cachedCart['grand_total'] ?? 0)) {
                report(new Exception("Amount mismatch: Stripe {$paymentIntent->amount} vs cached {$cachedCart['grand_total']}"));
                return $this->matchStatus('failed');
            }

            $lineItems = $cachedCart['line_items'];
            $shippingInformation = (object) array_merge(
                $cachedCart['metadata'] ?? [],
                ['line_items' => $lineItems]
            );

            $order = $this->orderService->create(
                $paymentIntent->id,
                self::GATEWAY,
                $shippingInformation->firstName ?? null,
                $shippingInformation->lastName ?? null,
                $shippingInformation->phoneNumber ?? null,
                $shippingInformation->email ?? null,
                $shippingInformation->shipOption ?? null,
                $shippingInformation->address ?? null,
                $shippingInformation->postCode ?? null,
                $shippingInformation->remarks ?? null,
                $paymentIntent->amount,
                $lineItems,
            );

            if (!$order->is_verified) {
                $this->orderService->markAsVerified($order->transaction_id);

                foreach ($lineItems as $item) {
                    try {
                        $this->itemService->decreaseStocks(
                            $item['item_id'], 
                            $item['quantity'], 
                            $item['size_variant_id'] ?? null, 
                            true
                        );
                    } catch(ItemStockCannotBeLowerThanZeroException $e) {
                        report($e);
                    }
                }
                SendOrderInvoice::dispatch($order);
            }
            
            if ($cartToken) {
                Cache::forget("cart_pending:{$cartToken}");
            }
        }

        return $this->matchStatus($paymentIntent->status);
    }
    

    public function createIndividualRegistration($discountcode, string $item, array $metadata = [])
    {
        $calculatedTotal = $this->calculateTotalIndividualRegistration($metadata['discountCodeId'], $item);
        $registrationKey = $metadata['registration_key'];

        $existingRegistration = IndividualRegistration::query()
            ->where('registration_key', $registrationKey)
            ->first();

        if ($existingRegistration) {
            if ((int) $existingRegistration->price === 0) {
                return response()->json([
                    'amount' => 0,
                    'transactionId' => $existingRegistration->transaction_id,
                ]);
            }

            $existingIntent = $this->retrievePaymentIntent($existingRegistration->transaction_id);

            Log::warning('Duplicate Weekly Series checkout reused existing payment', [
                'series_id' => (int) $item,
                'registration_key' => $registrationKey,
                'transaction_id' => $existingRegistration->transaction_id,
                'verified' => $existingRegistration->is_verified,
            ]);

            return response()->json([
                'stripeToken' => $existingIntent->client_secret,
                'paymentIntentId' => $existingIntent->id,
                'amount' => $existingIntent->amount,
                'alreadyPaid' => $existingIntent->status === PaymentIntent::STATUS_SUCCEEDED,
            ]);
        }

        $seriesItem = [
            'item_id' => $calculatedTotal['currentItem']->id,
            'price' => $calculatedTotal['regularPrice'],
            ];

        if($calculatedTotal['totalPrice'] !== 0) {
            $attempt = RegistrationPaymentAttempt::query()->firstOrCreate(
                ['registration_key' => $registrationKey],
                [
                    'series_id' => (int) $item,
                    'gateway' => self::GATEWAY->value,
                    'status' => 'created',
                ]
            );

            if ($attempt->gateway !== self::GATEWAY->value) {
                throw new HttpResponseException(response()->json([
                    'message' => 'This registration already has a payment in progress. Please check that payment instead.',
                ], 409));
            }

            if ($attempt->transaction_id) {
                $existingIntent = $this->retrievePaymentIntent($attempt->transaction_id);

                Log::warning('Duplicate Weekly Series payment attempt reused', [
                    'series_id' => (int) $item,
                    'registration_key' => $registrationKey,
                    'transaction_id' => $attempt->transaction_id,
                    'status' => $existingIntent->status,
                ]);

                return response()->json([
                    'stripeToken' => $existingIntent->client_secret,
                    'paymentIntentId' => $existingIntent->id,
                    'amount' => $existingIntent->amount,
                    'alreadyPaid' => $existingIntent->status === PaymentIntent::STATUS_SUCCEEDED,
                ]);
            }

            $metadata['line_item'] = json_encode($seriesItem);
            $metadata = array_filter(
                $metadata,
                fn ($value) => is_string($value) || is_numeric($value) || is_bool($value)
            );

            $productValue = [
                'amount' => $calculatedTotal['totalPrice'],
                'currency' => $this->currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $metadata,
            ];

            $paymentIntent = $this->stripe->paymentIntents->create(
                $productValue,
                ['idempotency_key' => "weekly-registration:{$registrationKey}"]
            );
            $attempt->update([
                'transaction_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
            ]);

            $responseValues = [
                'stripeToken' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $calculatedTotal['totalPrice'],
                'alreadyPaid' => $paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED,
            ];

            return response()->json($responseValues);
        }else{
            $paymentId = Uuid::uuid4()->toString();
            $shouldNotify = false;
            $seriesRegistered = DB::transaction(function () use (
                $paymentId,
                $metadata,
                $calculatedTotal,
                $seriesItem,
                $registrationKey,
                &$shouldNotify
            ) {
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
                    $seriesItem['item_id'],
                    $registrationKey,
                );

                if (! $seriesRegistered->is_verified) {
                    $this->individualRegistrationService->markAsVerified($seriesRegistered->transaction_id);
                    $this->incrementMaxRegistrationIfAllowed($seriesItem['item_id']);
                    $shouldNotify = true;
                }

                return $seriesRegistered;
            }, 3);

            if ($shouldNotify) {
                try {
                    $this->mailService->sendIndividualRegistrationInvoice(
                        $seriesRegistered->fresh(['item'])
                    );
                } catch (\Throwable $notificationError) {
                    Log::error('Free Weekly Series invoice notification failed after finalization', [
                        'transaction_id' => $seriesRegistered->transaction_id,
                        'registration_id' => $seriesRegistered->id,
                        'exception' => $notificationError,
                    ]);
                }
            }

            if (!empty($metadata['client_token'])) {
                WaitingLounge::where('client_id', $metadata['client_token'])
                    ->where('series_id', $seriesItem['item_id'])
                    ->delete();
            }

            return response()->json([
                'amount' => $calculatedTotal['totalPrice'],
                'transactionId' => $seriesRegistered->transaction_id
            ]);
        }
    }

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
        $existingRegistration = $this->individualRegistrationService
            ->findByTransactionId($paymentIntentId);

        if ($existingRegistration?->is_verified) {
            Log::info('Weekly Series verification replayed safely', [
                'transaction_id' => $paymentIntentId,
                'registration_id' => $existingRegistration->id,
            ]);

            return $this->matchStatus('succeeded');
        }

        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);
        RegistrationPaymentAttempt::query()
            ->where('transaction_id', $paymentIntentId)
            ->update(['status' => $paymentIntent->status]);
        
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

                $shouldNotify = false;
                $seriesRegistered = DB::transaction(function () use (
                    $paymentIntent,
                    $registrationInformation,
                    $lineItem,
                    &$shouldNotify
                ) {
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
                        (string) $registrationInformation->teamName,
                        (string) $registrationInformation->ageGroup,
                        $paymentIntent->amount,
                        $lineItem['item_id'],
                        $registrationInformation->registration_key ?? null,
                    );

                    if (! $seriesRegistered->is_verified) {
                        $this->individualRegistrationService
                            ->markAsVerified($seriesRegistered->transaction_id);
                        $this->incrementMaxRegistrationIfAllowed($lineItem['item_id']);
                        $shouldNotify = true;
                    }

                    return $seriesRegistered;
                }, 3);

                if ($shouldNotify) {
                    try {
                        $this->mailService->sendIndividualRegistrationInvoice(
                            $seriesRegistered->fresh(['item'])
                        );
                    } catch (\Throwable $notificationError) {
                        Log::error('Weekly Series invoice notification failed after payment finalization', [
                            'transaction_id' => $paymentIntent->id,
                            'registration_id' => $seriesRegistered->id,
                            'exception' => $notificationError,
                        ]);
                    }
                }

                RegistrationPaymentAttempt::query()
                    ->where('transaction_id', $paymentIntentId)
                    ->update(['status' => 'complete']);

                if (isset($registrationInformation->client_token)) {
                    WaitingLounge::where('client_id', $registrationInformation->client_token)
                        ->where('series_id', $lineItem['item_id'])
                        ->delete();
                }
            } catch (\Throwable $exception) {
                Log::error('Weekly Series payment finalization failed', [
                    'transaction_id' => $paymentIntent->id,
                    'exception' => $exception,
                ]);
                RegistrationPaymentAttempt::query()
                    ->where('transaction_id', $paymentIntentId)
                    ->update(['status' => 'finalization_pending']);

                return PaymentStatus::PROCESSING;
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

        if($discountCodeID !== 0 && !is_null($discountCodeID)){
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
        } catch (\Throwable $e) {
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
