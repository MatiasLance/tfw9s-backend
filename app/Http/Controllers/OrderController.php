<?php

namespace App\Http\Controllers;

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

    public function checkout(Request $request)
    {
        $items = $request->input('items');
        $metadata = $request->input('metadata');

        return $this->paymentService->createPaymentIntent($items, $metadata);
    }

    public function verify(Request $request, Message $message)
    {
        $paymentIntentId = $request->input('paymentIntent');

        $status = $this->paymentService->verify($paymentIntentId);

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
}
