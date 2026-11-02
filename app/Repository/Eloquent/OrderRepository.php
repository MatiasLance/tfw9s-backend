<?php

namespace App\Repository\Eloquent;

use App\Models\Order;
use App\Models\ShippingOptions;
use App\Modules\Order\Exceptions\AddressCannotBeEmptyException;
use App\Modules\Order\ShippingType;
use App\Modules\Payment\PaymentGateway;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{

    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByTransactionId(string $transactionId): ?Order
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }

    public function create(string $paymentIntentId, PaymentGateway $gateway, string $firstname, string $lastname, string $phoneNumber, string $email, ?string $shipOption, ?string $address, ?string $postCode, ?string $remarks, int $total, array $items, array $totals = [])
    {
        $existingOrder = $this->findByTransactionId($paymentIntentId);

        if (!is_null($existingOrder)) {
            return $existingOrder;
        }

        $order = new Order();
        $order->transaction_id = $paymentIntentId;
        $order->payment_gateway = $gateway;
        $order->firstname = $firstname;
        $order->lastname = $lastname;
        $order->phone_number = $phoneNumber;
        $order->email = $email;
        $order->ship_option = $shipOption;
        $order->is_verified = false;
        $order->address = $address;
        $order->post_code = $postCode;
        $order->remarks = $remarks ?? '';
        $order->total = $total;
        $order->sub_total = $totals['sub_total'] ?? null;
        $order->shipping_total = $totals['shipping_total'] ?? null;
        $order->tax_total = $totals['tax_total'] ?? null;

        DB::transaction(function() use($order, $items) {
            $order->save();
            $order->items()->createMany($items);
        });

        return $order;
    }

    public function updateShippingOptions(
        ?string $deliveryNote,
        ?string $pickupNote,
        ?int $deliveryCharge = null,
        ?int $pickupCharge = null
    ): bool
    {
        $options = $this->getOrCreateShippingOptions();

        if (!is_null($deliveryNote)) {
            $options->delivery_note = $deliveryNote;
        }

        if (!is_null($pickupNote)) {
            $options->pickup_note = $pickupNote;
        }

        if (!is_null($deliveryCharge)) {
            $options->delivery_charge = max(0, $deliveryCharge);
        }

        if (!is_null($pickupCharge)) {
            $options->pickup_charge = max(0, $pickupCharge);
        }

        return DB::transaction(function() use($options) {
            return $options->save();
        });
    }

    public function markAsVerified(string $transactionId): bool
    {
        $order = $this->findByTransactionId($transactionId);
        $order->is_verified = true;

        return DB::transaction(function() use($order) {
            return $order->save();
        });
    }

    public function retrieveShippingOptions(): ShippingOptions
    {
        return $this->getOrCreateShippingOptions();
    }

    /**
     * Retrieve the single shipping options row, creating it with safe
     * defaults if a deployment is missing the seed data.
     */
    private function getOrCreateShippingOptions(): ShippingOptions
    {
        $options = ShippingOptions::query()->first();

        if ($options) {
            return $options;
        }

        $options = new ShippingOptions();
        $options->delivery_note = '';
        $options->pickup_note = '';
        $options->delivery_charge = ShippingOptions::DEFAULT_DELIVERY_CHARGE;
        $options->pickup_charge = ShippingOptions::DEFAULT_PICKUP_CHARGE;
        $options->save();

        return $options;
    }
}