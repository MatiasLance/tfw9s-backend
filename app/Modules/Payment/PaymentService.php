<?php

namespace App\Modules\Payment;

use Stripe\StripeClient;
use App\Models\Shipping;
use App\Models\StateShipping;
use App\Models\CityShipping;
use App\Models\OtherCountryShipping;
use App\Models\OtherStateShipping;
use App\Models\OtherCityShipping;
use App\Models\Item;
use App\Models\Order;
use App\Modules\Item\Exceptions\ItemStockCannotBeLowerThanZeroException;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Mail\MailServiceInterface;
use App\Modules\Order\Exceptions\AddressCannotBeEmptyException;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Order\ShippingType;
use Stripe\PaymentIntent;

class PaymentService implements PaymentServiceInterface
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
     * Mail Service
     * 
     * @var OrderServiceInterface $orderService
     */
    protected OrderServiceInterface $orderService;

    /**
     * Mail Service
     * 
     * @var ItemServiceInterface $itemService
     */
    protected ItemServiceInterface $itemService;

    /**
     * Determines if the Stripe is live or test mode
     * 
     * @var bool $liveMode
     */
    protected bool $liveMode;

    public function __construct(MailServiceInterface $mailService, OrderServiceInterface $orderService, ItemServiceInterface $itemService)
    {
        $this->mailService = $mailService;
        $this->orderService = $orderService;
        $this->itemService = $itemService;
        $this->stripe = new StripeClient(env('STRIPE_API_SECRET_KEY'));
        $this->liveMode = env('STRIPE_LIVE_ENVIRONMENT', env('APP_ENV') === 'production');
    }

    public function createPaymentIntent(array $items, array $metadata = [], $currency = null): array
    {
        if (is_null($currency)) {
            $currency = self::CURRENCY;
        }
        
        if ($metadata['shippingType'] === ShippingType::DELIVERY) {
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
            'currency' => $currency,
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
    }

    public function verify(string $paymentIntentId)
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

        if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
            $shippingInformation = $paymentIntent->metadata;

            $lineItems = json_decode($shippingInformation->line_items, true);

            $order = $this->orderService->create(
                                            $paymentIntent->id,
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

            // If this is the first time the order is recorded
            if ($order instanceof Order) {
                foreach ($lineItems as $item) {
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

        return $paymentIntent->status;
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
}