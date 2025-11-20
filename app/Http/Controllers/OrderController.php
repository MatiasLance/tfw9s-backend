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
            
            // Check if this is a size variant item
            $sizeVariantId = $item['size_variant_id'] ?? null;
            $sizeVariantPrice = null;
            
            // Get price from size variant if available
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

            // Determine the base price (size variant price or item price)
            $regularPrice = $sizeVariantPrice ?? $currentItem->centPrice();
            $salePrice = $currentItem->centSalePrice();
            $onSale = $currentItem->isOnSale();

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
                'size_variant_id' => $sizeVariantId,
                'price' => $price,
                'quantity' => $item['quantity'],
            ];

            array_push($lineItems, $lineItem);
        }

        $totalProduct = $this->calculateTotal($discountcode, $lineItems);

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
            
            return response()->json([
                'subtotal' => $productBase / 100,
                'shipping' => $shippingBase / 100,
                'tax' => $taxAmount / 100,
                'total' => ($productBase + $shippingBase) / 100,
                'grand_total' => $grandTotal / 100,
                'is_inclusive' => true
            ]);
            
        } else {
            // EXCLUSIVE MODE: Tax added to both products AND shipping
            $taxAmount = ($productTotal + $shippingFee) * ($addTax / 100);
            $grandTotal = $productTotal + $shippingFee + $taxAmount;
            
            return response()->json([
                'subtotal' => $productTotal / 100,
                'shipping' => $shippingFee / 100,
                'tax' => $taxAmount / 100,
                'total' => ($productTotal + $shippingFee) / 100,
                'grand_total' => $grandTotal / 100,
                'is_inclusive' => false
            ]);
        }
    }

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
