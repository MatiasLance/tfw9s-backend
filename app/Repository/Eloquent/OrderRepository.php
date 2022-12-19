<?php

namespace App\Repository\Eloquent;

use App\Models\Order;
use App\Models\ShippingOptions;
use App\Modules\Order\Exceptions\AddressCannotBeEmptyException;
use App\Modules\Order\ShippingType;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{

    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByTransactionId(string $transactionId): Order
    {
        return $this->model->where('payment_intent_id', $transactionId)->first();
    }

    public function create(string $paymentIntentId, string $firstname, string $lastname, string $phoneNumber, string $email, string $shippingType, ?string $address, ?string $postCode, ?string $remarks, int $total, array $items)
    {
        $existingOrder = $this->findByTransactionId($paymentIntentId);

        if (!is_null($existingOrder)) {
            return $existingOrder;
        }

        $order = new Order();
        $order->payment_intent_id = $paymentIntentId;
        $order->firstname = $firstname;
        $order->lastname = $lastname;
        $order->phone_number = $phoneNumber;
        $order->email = $email;
        $order->shipping_type = $shippingType;
        if ($shippingType == ShippingType::DELIVERY) {

            if (
                !isset($address) ||
                empty($address) ||
                !isset($postCode) ||
                empty($postCode)
            ) {
                report(new AddressCannotBeEmptyException('Recorded a finished delivery order but without a shipping address'));
            }

            $order->address = $address;
            $order->post_code = $postCode;
        }
        $order->remarks = $remarks;
        $order->total = $total;

        DB::transaction(function() use($order, $items) {
            $order->save();
            $order->items()->createMany($items);
        });

        return $order;
    }

    public function updateShippingOptions(?string $deliveryNote, ?string $pickupNote): bool
    {
        $options = ShippingOptions::first();
        
        if (!is_null($deliveryNote)) {
            $options->delivery_note = $deliveryNote;
        }

        if (!is_null($pickupNote)) {
            $options->pickup_note = $pickupNote;
        }

        return DB::transaction(function() use($options) {
            return $options->save();
        });
    }

    public function retrieveShippingOptions(): ShippingOptions
    {
        return ShippingOptions::first();
    }
}