<?php

namespace App\Modules\Payment\Gateways;

use App\Models\Item;
use App\Models\Shipping;
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

    public function __construct(MailServiceInterface $mailService, OrderServiceInterface $orderService, ItemServiceInterface $itemService, array $config = [])
    {
        $this->mailService = $mailService;
        $this->orderService = $orderService;
        $this->itemService = $itemService;
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
    public function createOrder(array $items, array $metadata = [])
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

        $shippingchoicecalc = $metadata['shippingChoiceCalc'];

        $totalshipping = $this->calculateTotal($items, $shippingchoicecalc);
        $itemSubtotal = $totalshipping['totalProduct'] + $totalshipping['totalShipping'];
        $total = intval(($itemSubtotal * 0.1) + $itemSubtotal);
 
        // $total = $this->calculateTotal($items);

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

        $metadata['line_items'] = json_encode($lineItems);

        $productValue = [
            'amount' => $total,
            'currency' => $this->currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => $metadata,
        ];
        
        $paymentIntent = $this->stripe->paymentIntents->create($productValue);
        return [
            'totalProduct' => $totalshipping['totalProduct'],
            'totalShipping' => $totalshipping['totalShipping'],
            'stripeToken' => $paymentIntent->client_secret
        ];
        
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
    protected function calculateTotal(array $items, $shippingchoicecalc): array
    {
        // $total = 0;
        // foreach ($items as $item) {
        //     $price = Item::find($item['id'])->centPrice();
        //     $unitPrice = $price * $item['quantity'];
        //     $gstPrice = ($unitPrice * 10) / 100;
        //     $total += $unitPrice + $gstPrice;
        // }
        // return $total;

        $total = 0;
        $tot = 0;
        foreach ($items as $item) {
            if($shippingchoicecalc == "Own Country"){
                $data = Shipping::latest()->first();
                $sv = $data->shipping_value;
                $iv = $data->insurance_value;
                $rv = $data->registered_value;
                $ev = $data->express_value;
                $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);

                $price = Item::find($item['id'])->centPrice();
                $unitPrice = $price * $item['quantity'];
                $gstPrice = ($unitPrice * 10) / 100;
                $total += $unitPrice;
            }elseif($shippingchoicecalc == "Own State"){
                $data = StateShipping::latest()->first();
                $sv = $data->shipping_value;
                $iv = $data->insurance_value;
                $rv = $data->registered_value;
                $ev = $data->express_value;
                $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                $price = Item::find($item['id'])->centPrice();
                $unitPrice = $price * $item['quantity'];
                $gstPrice = ($unitPrice * 10) / 100;
                $total += $unitPrice;
            }elseif($shippingchoicecalc == "Own City"){
                $data = CityShipping::latest()->first();
                $sv = $data->shipping_value;
                $iv = $data->insurance_value;
                $rv = $data->registered_value;
                $ev = $data->express_value;
                $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                $price = Item::find($item['id'])->centPrice();
                $unitPrice = $price * $item['quantity'];
                $gstPrice = ($unitPrice * 10) / 100;
                $total += $unitPrice;
            }elseif($shippingchoicecalc == "Other Country"){
                $data = OtherCountryShipping::latest()->first();
                $sv = $data->shipping_value;
                $iv = $data->insurance_value;
                $rv = $data->registered_value;
                $ev = $data->express_value;
                $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                $price = Item::find($item['id'])->centPrice();
                $unitPrice = $price * $item['quantity'];
                $gstPrice = ($unitPrice * 10) / 100;
                $total += $unitPrice;
            }elseif($shippingchoicecalc == "Other State"){
                $data = OtherStateShipping::latest()->first();
                $sv = $data->shipping_value;
                $iv = $data->insurance_value;
                $rv = $data->registered_value;
                $ev = $data->express_value;
                $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                $price = Item::find($item['id'])->centPrice();
                $unitPrice = $price * $item['quantity'];
                $gstPrice = ($unitPrice * 10) / 100;
                $total += $unitPrice;
            }elseif($shippingchoicecalc == "Other City"){
                $data = OtherCityShipping::latest()->first();
                $sv = $data->shipping_value;
                $iv = $data->insurance_value;
                $rv = $data->registered_value;
                $ev = $data->express_value;
                $tot += intval($sv) + intval($iv) + intval($rv) + intval($ev);
                $price = Item::find($item['id'])->centPrice();
                $unitPrice = $price * $item['quantity'];
                $gstPrice = ($unitPrice * 10) / 100;
                $total += $unitPrice;
            }
        }
        return [
            'totalProduct' => $total,
            'totalShipping' => $tot
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
}