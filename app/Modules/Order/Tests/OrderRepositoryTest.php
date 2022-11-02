<?php

namespace App\Modules\Order\Tests;

use App\Models\Item;
use App\Models\Order;
use App\Modules\Order\Exceptions\AddressCannotBeEmptyException;
use App\Modules\Order\ShippingType;
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
                    'Test',
                    'Customer',
                    '125917',
                    'test@email.com',
                    ShippingType::DELIVERY,
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
                    'Test',
                    'Customer',
                    '125917',
                    'test@email.com',
                    ShippingType::PICKUP,
                    null,
                    null,
                    'This is a test payment',
                    $orderTotal,
                    $lineItems
                );

        $this->assertTrue($order instanceof Order);
        $this->assertCount(count($lineItems), $order->items);
    }
}