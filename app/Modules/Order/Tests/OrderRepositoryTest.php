<?php

namespace App\Modules\Order\Tests;

use App\Models\Item;
use App\Models\Order;
use App\Modules\Order\Exceptions\AddressCannotBeEmptyException;
use App\Modules\Order\ShippingType;
use App\Modules\Payment\PaymentGateway;
use App\Repository\OrderRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    public function test_create_delivery_order()
    {
        $item = Item::factory()->create();
        
        $orderRepository = $this->app->make(OrderRepositoryInterface::class);

        $lineItems = [
            [
                'item_id' => $item->id,
                'price' => $item->centPrice(),
                'quantity' => 1,
            ]
        ];

        $orderTotal = 0;

        foreach ($lineItems as $lineItem) {
            $subTotal = $lineItem['price'] * $lineItem['quantity'];
            $orderTotal += $subTotal;
        }

        $order = $orderRepository->create(
                    1,
                    PaymentGateway::STRIPE,
                    'Test',
                    'Customer',
                    '125917',
                    'test@email.com',
                    ShippingType::DELIVERY->value,
                    'Test Address',
                    '6244',
                    'This is a test payment',
                    $orderTotal,
                    $lineItems
                );

        $this->assertTrue($order instanceof Order);
        $this->assertCount(count($lineItems), $order->items);
    }

    public function test_create_pickup_order()
    {
        $item = Item::factory()->create();
        
        $orderRepository = $this->app->make(OrderRepositoryInterface::class);

        $lineItems = [
            [
                'item_id' => $item->id,
                'price' => $item->centPrice(),
                'quantity' => 1,
            ]
        ];

        $orderTotal = 0;

        foreach ($lineItems as $lineItem) {
            $subTotal = $lineItem['price'] * $lineItem['quantity'];
            $orderTotal += $subTotal;
        }

        $order = $orderRepository->create(
                    1,
                    PaymentGateway::STRIPE,
                    'Test',
                    'Customer',
                    '125917',
                    'test@email.com',
                    ShippingType::PICKUP->value,
                    null,
                    null,
                    'This is a test payment',
                    $orderTotal,
                    $lineItems
                );

        $this->assertTrue($order instanceof Order);
        $this->assertCount(count($lineItems), $order->items);
    }

    public function test_create_order_persists_the_totals_breakdown()
    {
        $item = Item::factory()->create();

        $orderRepository = $this->app->make(OrderRepositoryInterface::class);

        $lineItems = [
            [
                'item_id' => $item->id,
                'price' => 4000,
                'quantity' => 1,
            ]
        ];

        $totals = [
            'sub_total' => 4000,
            'shipping_total' => 1000,
            'tax_total' => 0,
            'grand_total' => 5000,
        ];

        $order = $orderRepository->create(
                    'totals-breakdown-' . uniqid(),
                    PaymentGateway::STRIPE,
                    'Test',
                    'Customer',
                    '125917',
                    'test@email.com',
                    ShippingType::DELIVERY->value,
                    'Test Address',
                    '6244',
                    'This is a test payment',
                    $totals['grand_total'],
                    $lineItems,
                    $totals
                );

        $order->refresh();

        $this->assertTrue($order->hasPersistedTotals());
        $this->assertSame(4000, $order->sub_total);
        $this->assertSame(1000, $order->shipping_total);
        $this->assertSame(0, $order->tax_total);
        $this->assertSame(5000, $order->total);
        $this->assertSame(
            $order->total,
            $order->sub_total + $order->shipping_total + $order->tax_total
        );
    }
}