<?php

namespace App\Http\Controllers;

use App\Models\NewShipping;
use App\Models\MasterShippingSetting;
use App\Models\StateShipping;
use App\Models\CityShipping;
use App\Models\OtherCountryShipping;
use App\Models\OtherStateShipping;
use App\Models\OtherCityShipping;
use App\Models\Item;
use App\Models\DiscountCode;
use App\Modules\Http\Message;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Payment\PaymentServiceInterface;
use Illuminate\Http\Request;
use App\Models\Tax;
use App\Models\ToggleTaxControl;

class OrderController extends Controller
{
    /**
     * Item service
     *
     * @var ItemServiceInterface $itemService
     */
    protected ItemServiceInterface $itemService;

    /**
     * Payment service
     *
     * @var PaymentServiceInterface $paymentService
     */
    protected PaymentServiceInterface $paymentService;

    /**
     * Order service
     *
     * @var OrderServiceInterface $orderService
     */
    protected OrderServiceInterface $orderService;

    public function __construct(ItemServiceInterface $itemService, PaymentServiceInterface $paymentService, OrderServiceInterface $orderService)
    {
        $this->itemService = $itemService;
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
    }

    /**
     * @todo Forgot to use Message class to render response here. Need to make sure frontend also adjusts to the new response structure
     */
    public function checkout(Request $request)
    {
        $items = $request->input('items');
        $metadata = $request->input('metadata') ?? [];
        $paymentMethod = $request->input('payment_method');
        $discountcode = $request->input('discountcode');

        return $this->paymentService->createOrder($discountcode, $paymentMethod, $items, $metadata);
    }

    public function verify(Request $request, Message $message)
    {
        $paymentIntentId = $request->input('transaction_id');
        $paymentMethod = $request->input('payment_method');

        $status = $this->paymentService->verify($paymentMethod, $paymentIntentId);

        $message->setContent(200, 'Payment Intent status found', '', [
            'status' => $status
        ]);

        return $message->render();
    }

    public function retrieveShippingOptions(Request $request, Message $message)
    {
        $options = $this->orderService->retrieveShippingOptions();

        $message->setContent(200, 'Shipping options retrieved', '', [
            'options' => $options
        ]);

        return $message->render();
    }

    public function updateShippingOptions(Request $request, Message $message)
    {
        $deliveryNote = $request->input('delivery_note', null);
        $pickupNote = $request->input('pickup_note', null);

        $isSuccess = $this->orderService->updateShippingOptions($deliveryNote, $pickupNote);

        if ($isSuccess) {
            $message->setContent(200, 'Shipping notes updated');
        } else {
            $message->setContent(500, 'Shipping notes update failed. Please try again');
        }

        return $message->render();
    }

    public function shippingCalc(Request $request, array $items, array $metadata = [])
    {
        $items = $request->input('items');
        $metadata = $request->input('metadata') ?? [];
        $discountcode = $request->input('discountcode', null);
        $result = DiscountCode::where('code', $discountcode)->first();
        $hasDiscount = !empty($result);

        $lineItems = [];

        foreach ($items as $item) {
            $currentItem = Item::find($item['id']);
            $onSale = $currentItem->isOnSale();
            $salePrice = $currentItem->centSalePrice();
            $regularPrice = $currentItem->centPrice();

            if ($onSale && $hasDiscount) {
                $price = $salePrice * (1 - $result->rate);
            } elseif ($onSale && !$hasDiscount) {
                $price = $salePrice;
            } elseif (!$onSale && $hasDiscount) {
                $price = $regularPrice * (1 - $result->rate);
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


        $totalProduct = $this->calculateTotal($discountcode, $lineItems);

        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        $taxAmount = 0;
        $totalPrice = 0;
        $total = 0;

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $gstInclusive = $master->toggleControl2;
        $gstExclusive = $master->toggleControl1;

        if (!$gstInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $totalProduct['totalProduct'] * $taxRate;
            $totalPrice = intval($totalProduct['totalProduct'] + $taxAmount);
            $gstInclusive = false;
        } elseif ($gstInclusive) {
            $taxRate = $includeTax / 100;
            $taxAmount = $totalProduct['totalProduct'] * $taxRate;
            $totalPrice = intval($totalProduct['totalProduct'] );
            $gstInclusive = true;
        } else {
            $totalPrice = intval($totalProduct['totalProduct']);
            $gstInclusive = true;
        }

        $totalProduct['taxAmount'] = $taxAmount;

        if($metadata['shipOption'] === 'delivery') {
            $total = ($totalPrice / 100) + 10;
        }else{
            $total = $totalPrice;
        }

        $toCents = intval($total * 100);

        return response()->json([
            'overAllTotal' => $toCents
        ]);

    }

    protected function calculateTotal($discountcode, array $items): array
    {
        $total = 0;
        foreach ($items as $item) {
           $total += $this->calculateItemTotal($discountcode, $item['item_id'], $item['quantity']);
        }

        return ['totalProduct' => $total];
    }

    protected function calculateItemTotal($discountcode, int $itemId, int $quantity): float
    {
        $res = DiscountCode::where('code', $discountcode)->first();

        $item = $this->itemService->retrieveItem($itemId);
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

    public function refundRegistration(Request $request)
    {
        $transaction_id = $request->input('transaction_id');
        $amount = $request->input('amount');
        $method = $request->input('method');

        return $this->paymentService->registrationRefund($method, $transaction_id, $amount);
    }

    public function cancelRefund(Request $request)
    {
        $transaction_id = $request->input('transaction_id');
        $method = $request->input('method');

        return $this->paymentService->cancelRefund($method, $transaction_id);
    }
    
}
