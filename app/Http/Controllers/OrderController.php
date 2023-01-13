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
use App\Modules\Http\Message;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Payment\PaymentServiceInterface;
use Illuminate\Http\Request;

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

        return $this->paymentService->createOrder($paymentMethod, $items, $metadata);
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

        // Added from WPI
        $shippingchoicecalc = $metadata['shippingChoiceCalc'];
        $shippingoptions = $metadata['shippingOptions']['selected'];

        $registeredpost = in_array('Registered Value', $shippingoptions);
        $expresspost = in_array('Express Value', $shippingoptions);
        $addinsurance = in_array('Insurance Value', $shippingoptions);

        $totalshipping = $this->calculateTotal($lineItems, $shippingchoicecalc, $registeredpost, $expresspost, $addinsurance);

        return response()->json([
               'shippingCalculation' => $totalshipping
        ]);

    }

    protected function calculateTotal(array $items, $shippingchoicecalc, $registeredpost, $expresspost, $addinsurance): array
    {
        $total = 0;
        $tot = 0;
        foreach ($items as $item) {

            if($shippingchoicecalc == "Own Country"){
                $data = NewShipping::latest()->first();

                $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
                $tot += intval($data->shippingCentPrice());
                if($registeredpost){
                    $rv = $data->registeredCentPrice();
                    $tot += intval($rv);
                
                }
                if($expresspost){
                    $ev = $data->expressCentPrice();
                    $tot += intval($ev);
                }
                if($addinsurance){
                    $iv = $data->insuranceCentPrice();
                    $tot += intval($iv);
                }

            }elseif($shippingchoicecalc == "Own State"){
                $data = StateShipping::latest()->first();

                $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
                $tot += intval($data->shippingCentPrice());

                if($registeredpost){
                    $rv = $data->registeredCentPrice();
                    $tot += intval($rv);
                }
                if($expresspost){
                    $ev = $data->expressCentPrice();
                    $tot += intval($ev);
                }
                if($addinsurance){
                    $iv = $data->insuranceCentPrice();
                    $tot += intval($iv);
                }
            }elseif($shippingchoicecalc == "Own City"){
                $data = CityShipping::latest()->first();

                $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
                $tot += intval($data->shippingCentPrice());

                if($registeredpost){
                    $rv = $data->registeredCentPrice();
                    $tot += intval($rv);
                }
                if($expresspost){
                    $ev = $data->expressCentPrice();
                    $tot += intval($ev);
                }
                if($addinsurance){
                    $iv = $data->insuranceCentPrice();
                    $tot += intval($iv);
                }
            }elseif($shippingchoicecalc == "Other Country"){
                $data = OtherCountryShipping::latest()->first();

                $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
                $tot += intval($data->shippingCentPrice());

                if($registeredpost){
                    $rv = $data->registeredCentPrice();
                    $tot += intval($rv);
                }
                if($expresspost){
                    $ev = $data->expressCentPrice();
                    $tot += intval($ev);
                }
                if($addinsurance){
                    $iv = $data->insuranceCentPrice();
                    $tot += intval($iv);
                }
            }elseif($shippingchoicecalc == "Other State"){
                $data = OtherStateShipping::latest()->first();

                $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
                $tot += intval($data->shippingCentPrice());

                if($registeredpost){
                    $rv = $data->registeredCentPrice();
                    $tot += intval($rv);
                }
                if($expresspost){
                    $ev = $data->expressCentPrice();
                    $tot += intval($ev);
                }
                if($addinsurance){
                    $iv = $data->insuranceCentPrice();
                    $tot += intval($iv);
                }
            }elseif($shippingchoicecalc == "Other City"){
                $data = OtherCityShipping::latest()->first();

                $total += $this->calculateItemTotal($item['item_id'], $item['quantity']);
                $tot += intval($data->shippingCentPrice());
                
                if($registeredpost){
                    $rv = $data->registeredCentPrice();
                    $tot += intval($rv);
                }
                if($expresspost){
                    $ev = $data->expressCentPrice();
                    $tot += intval($ev);
                }
                if($addinsurance){
                    $iv = $data->insuranceCentPrice();
                    $tot += intval($iv);
                }
            }
        }

        $data = MasterShippingSetting::latest()->first();
        if($total > 100) {
            $total + intval($data->maxshipping_value);
        }
        

        return [
            'totalProduct' => $total,
            'totalShipping' => $tot
        ];
    }

    protected function calculateItemTotal(int $itemId, int $quantity): float
    {
        $item = $this->itemService->retrieveItem($itemId);
        return $item->centPrice() * $quantity;
    }
}
