<?php

namespace App\Modules\Payment\Gateways;

use App\Models\Series;
use App\Models\Item;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use App\Models\DiscountCode;
use App\Models\NewShipping;
use App\Models\StateShipping;
use App\Models\CityShipping;
use App\Models\OtherCountryShipping;
use App\Models\OtherStateShipping;
use App\Models\OtherCityShipping;
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
    public function createOrder($discountcode, array $items, array $metadata = [])
    {
        // @todo remove !empty() and reevaluate code block
        if (!empty($metadata) && $metadata['shippingType'] === ShippingType::DELIVERY) {
            if (
                !isset($metadata['address']) ||
                empty($metadata['address']) ||
                !isset($metadata['postCode']) ||
                empty($metadata['postCode']) ||
                !isset($metadata['shippingChoiceCalc']) ||
                empty($metadata['shippingChoiceCalc'])
            ) {
                throw new AddressCannotBeEmptyException('Attempted to create a payment intent for delivery order without address');
            }
        }
        $res = DiscountCode::where('code', $discountcode)->first();

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
                'price' => $price,
                'quantity' => $item['quantity'],
            ];
            array_push($lineItems, $lineItem);
        }


        $shippingchoicecalc = $metadata['shippingChoiceCalc'];

        $totalshipping = $this->calculateTotal($discountcode, $items, $shippingchoicecalc);

        $itemSubtotal = $totalshipping['totalProduct'] + $totalshipping['totalShipping'];

        $total = $itemSubtotal;

        $metadata['line_items'] = json_encode($lineItems);

        unset($metadata['shippingOptions']);

        $productValue = [
            'amount' => $total,
            'currency' => $this->currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => $metadata,
        ];

        $paymentIntent = $this->stripe->paymentIntents->create($productValue);

        $responseValues = [
            'totalProduct' => $totalshipping['totalProduct'],
            'totalShipping' => $totalshipping['totalShipping'],
            'stripeToken' => $paymentIntent->client_secret
        ];

        return response()->json($responseValues);

        // return $paymentIntent->client_secret;
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
                                            $shippingInformation->shippingType,
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
                        $this->itemService->decreaseStocks($item['item_id'], $item['quantity'], true);
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
            $taxRate = $addTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = false;
        }

        $seriesItem = [
            'item_id' => $currentItem->id,
            'price' => $regularPrice,
        ];

        $metadata['line_item'] = json_encode($seriesItem);

        $productValue = [
            'amount' => $totalPrice,
            'currency' => $this->currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => $metadata,
        ];

        $paymentIntent = $this->stripe->paymentIntents->create($productValue);

        $responseValues = [
            'stripeToken' => $paymentIntent->client_secret
        ];

        return response()->json($responseValues);

        // return $paymentIntent->client_secret;
    }

    public function verifyIndividualRegistration(string $paymentIntentId): PaymentStatus
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

        if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $registrationInformation = $paymentIntent->metadata;

            $lineItems = json_decode($registrationInformation->line_items, true);

            $seriesRegistered = $this->individualRegistrationService->create(
                                            $paymentIntent->id,
                                            self::GATEWAY,
                                            $registrationInformation->contactEmail,
                                            $registrationInformation->contactFirstName,
                                            $registrationInformation->contactLastName,
                                            $registrationInformation->contactPhoneNumber,
                                            $registrationInformation->playerFirstName,
                                            $registrationInformation->playerLastName,
                                            $registrationInformation->dob,
                                            $registrationInformation->teamName,
                                            $registrationInformation->ageGroup,
                                            $paymentIntent->amount,
                                            $lineItems,
                                        );


            if (!$seriesRegistered->is_verified) {
                $this->individualRegistrationService->markAsVerified($seriesRegistered->transaction_id);

                // $this->mailService->sendInvoice($seriesRegistered);
            }
        }

        return $this->matchStatus($paymentIntent->status);
    }

    public function verifyTeamRegistration(string $paymentIntentId): PaymentStatus
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

        if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $registrationInformation = $paymentIntent->metadata;

            $lineItems = json_decode($registrationInformation->line_item, true);

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
                                            $paymentIntent->amount,
                                            $lineItems,
                                        );


            if (!$seriesRegistered->is_verified) {
                $this->teamRegistrationService->markAsVerified($seriesRegistered->transaction_id);

                // $this->mailService->sendInvoice($seriesRegistered);
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
    protected function calculateTotal($discountcode, array $items, $shippingchoicecalc): array
    {
        $total = 0;
        $tot = 0;
        foreach ($items as $item) {
            if (!empty($shippingchoicecalc)) {
                if($shippingchoicecalc == "Own Country") {
                    $data = NewShipping::latest()->first();
                    $sv = $data->shipping_value;
                    $iv = $data->insurance_value;
                    $rv = $data->registered_value;
                    $ev = $data->express_value;
                    $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);

                    $total += $this->calculateItemTotal($discountcode, $item['id'], $item['quantity']);

                }elseif($shippingchoicecalc == "Own State"){
                    $data = StateShipping::latest()->first();
                    $sv = $data->shipping_value;
                    $iv = $data->insurance_value;
                    $rv = $data->registered_value;
                    $ev = $data->express_value;
                    $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                    $total += $this->calculateItemTotal($discountcode, $item['id'], $item['quantity']);
                }elseif($shippingchoicecalc == "Own City"){
                    $data = CityShipping::latest()->first();
                    $sv = $data->shipping_value;
                    $iv = $data->insurance_value;
                    $rv = $data->registered_value;
                    $ev = $data->express_value;
                    $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                    $total += $this->calculateItemTotal($discountcode, $item['id'], $item['quantity']);
                }elseif($shippingchoicecalc == "Other Country"){
                    $data = OtherCountryShipping::latest()->first();
                    $sv = $data->shipping_value;
                    $iv = $data->insurance_value;
                    $rv = $data->registered_value;
                    $ev = $data->express_value;
                    $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                    $total += $this->calculateItemTotal($discountcode, $item['id'], $item['quantity']);
                }elseif($shippingchoicecalc == "Other State"){
                    $data = OtherStateShipping::latest()->first();
                    $sv = $data->shipping_value;
                    $iv = $data->insurance_value;
                    $rv = $data->registered_value;
                    $ev = $data->express_value;
                    $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                    $total += $this->calculateItemTotal($discountcode, $item['id'], $item['quantity']);
                }elseif($shippingchoicecalc == "Other City"){
                    $data = OtherCityShipping::latest()->first();
                    $sv = $data->shipping_value;
                    $iv = $data->insurance_value;
                    $rv = $data->registered_value;
                    $ev = $data->express_value;
                    $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                    $total += $this->calculateItemTotal($discountcode, $item['id'], $item['quantity']);
                }
            } else {
                $tot = 0;
                $total += $this->calculateItemTotal($discountcode, $item['id'], $item['quantity']);
            }
        }
        return [
            'totalProduct' => $total,
            'totalShipping' => $tot
        ];
    }

    /**
     * Calculate total item price with quantity taken into consideration
     *
     * @param int $itemId ID of the item
     * @param int $quantity
     *
     * @return float
     */
    protected function calculateItemTotal($discountcode, int $itemId, int $quantity): float
    {
        $item = $this->itemService->retrieveItem($itemId);
        $res = DiscountCode::where('code', $discountcode)->first();

        $onSale = $item->isOnSale();
        $hasDiscount = !empty($discountcode);
        $salePrice = $item->centSalePrice();
        $regularPrice = $item->centPrice();

        if ($onSale && $hasDiscount) {
            $dprice = $salePrice * (1 - $res->rate);
        } elseif ($onSale && !$hasDiscount) {
            $dprice = $salePrice;
        } elseif (!$onSale && $hasDiscount) {
            $dprice = $regularPrice * (1 - $res->rate);
        } else {
            $dprice = $regularPrice;
        }

        $price = $dprice;
        return $price * $quantity;
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
}
