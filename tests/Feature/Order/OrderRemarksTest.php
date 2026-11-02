<?php

namespace Tests\Feature\Order;

use App\Models\Item;
use App\Models\Order;
use App\Modules\Order\ShippingType;
use App\Modules\Payment\PaymentGateway;
use App\Repository\OrderRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRemarksTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created_without_remarks(): void
    {
        $item = Item::factory()->create();

        $lineItems = [
            [
                'item_id' => $item->id,
                'price' => 4000,
                'quantity' => 1,
            ],
        ];

        $order = $this->app->make(OrderRepositoryInterface::class)->create(
            'remarks-null-' . uniqid(),
            PaymentGateway::STRIPE,
            'Dev',
            'Lance',
            '+61485906541',
            'dev.pageone247@gmail.com',
            ShippingType::DELIVERY->value,
            '60 Manning Street',
            '2430',
            null,
            5000,
            $lineItems,
            [
                'sub_total' => 4000,
                'shipping_total' => 1000,
                'tax_total' => 0,
            ]
        );

        $order->refresh();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame('', $order->remarks);
        $this->assertSame(5000, $order->total);
    }
}
