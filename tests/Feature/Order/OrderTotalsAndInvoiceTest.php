<?php

namespace Tests\Feature\Order;

use App\Mail\Orders\Invoice;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\ShippingOptions;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use App\Modules\Payment\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTotalsAndInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function configureTax(float $rate, bool $inclusive): void
    {
        Tax::factory()->create([
            'addTaxValue' => $rate,
            'includeTaxValue' => $rate,
        ]);

        ToggleTaxControl::factory()->create([
            'toggleControl1' => $inclusive,
            'toggleControl2' => $inclusive,
        ]);
    }

    private function configureShipping(int $deliveryCharge, int $pickupCharge): void
    {
        $options = ShippingOptions::query()->first()
            ?? new ShippingOptions(['delivery_note' => '', 'pickup_note' => '']);

        $options->delivery_charge = $deliveryCharge;
        $options->pickup_charge = $pickupCharge;
        $options->save();
    }

    private function makeItem(float $price, float $shippingCharge = 10, bool $hasShipping = true): Item
    {
        return Item::factory()->create([
            'price' => $price,
            'saleprice' => 0,
            'is_on_sale' => false,
            'is_active' => true,
            'stock' => 10,
            'has_shipping' => $hasShipping,
            'shipping_charge' => $shippingCharge,
        ]);
    }

    private function makeOrder(Item $item, array $totals = [], ?string $shipOption = 'delivery'): Order
    {
        $order = new Order();
        $order->transaction_id = 'txn_' . uniqid();
        $order->payment_gateway = PaymentGateway::STRIPE;
        $order->firstname = 'Test';
        $order->lastname = 'Customer';
        $order->phone_number = '0400000000';
        $order->email = 'customer@example.test';
        $order->ship_option = $shipOption;
        $order->address = '1 Test Street';
        $order->post_code = '2000';
        $order->remarks = 'Test order';
        $order->total = $totals['grand_total'] ?? 0;
        $order->sub_total = $totals['sub_total'] ?? null;
        $order->shipping_total = $totals['shipping_total'] ?? null;
        $order->tax_total = $totals['tax_total'] ?? null;
        $order->save();

        $order->items()->create([
            'item_id' => $item->id,
            'price' => $item->centPrice(),
            'quantity' => 1,
        ]);

        return $order->fresh(['items.item']);
    }

    public function test_calculation_endpoint_includes_shipping_in_the_grand_total(): void
    {
        $this->configureTax(0, false);
        $item = $this->makeItem(40, 10);

        $response = $this->postJson('/api/v1/orders/calculation', [
            'items' => [
                ['id' => $item->id, 'quantity' => 1],
            ],
            'metadata' => ['shipOption' => 'delivery'],
        ]);

        $response->assertOk();

        $this->assertEquals(40, $response->json('subtotal'));
        $this->assertEquals(10, $response->json('shipping'));
        $this->assertEquals(0, $response->json('tax'));
        $this->assertEquals(50, $response->json('grand_total'));
    }

    public function test_calculation_endpoint_does_not_charge_shipping_for_pickup(): void
    {
        $this->configureTax(0, false);
        $item = $this->makeItem(40, 10);

        $response = $this->postJson('/api/v1/orders/calculation', [
            'items' => [
                ['id' => $item->id, 'quantity' => 1],
            ],
            'metadata' => ['shipOption' => 'pickup'],
        ]);

        $response->assertOk();

        $this->assertEquals(40, $response->json('subtotal'));
        $this->assertEquals(0, $response->json('shipping'));
        $this->assertEquals(40, $response->json('grand_total'));
    }

    public function test_calculation_endpoint_uses_the_configured_delivery_charge(): void
    {
        $this->configureTax(0, false);
        $this->configureShipping(1250, 0);
        $item = $this->makeItem(40, 10);

        $response = $this->postJson('/api/v1/orders/calculation', [
            'items' => [
                ['id' => $item->id, 'quantity' => 1],
            ],
            'metadata' => ['shipOption' => 'delivery'],
        ]);

        $response->assertOk();

        $this->assertEquals(40, $response->json('subtotal'));
        $this->assertEquals(12.5, $response->json('shipping'));
        $this->assertEquals(52.5, $response->json('grand_total'));
    }

    public function test_calculation_endpoint_uses_the_configured_pickup_charge(): void
    {
        $this->configureTax(0, false);
        $this->configureShipping(1000, 250);
        $item = $this->makeItem(40, 10);

        $response = $this->postJson('/api/v1/orders/calculation', [
            'items' => [
                ['id' => $item->id, 'quantity' => 1],
            ],
            'metadata' => ['shipOption' => 'pickup'],
        ]);

        $response->assertOk();

        $this->assertEquals(40, $response->json('subtotal'));
        $this->assertEquals(2.5, $response->json('shipping'));
        $this->assertEquals(42.5, $response->json('grand_total'));
    }

    public function test_calculation_endpoint_charges_one_delivery_fee_for_multiple_items(): void
    {
        $this->configureTax(0, false);
        $firstItem = $this->makeItem(40, 10);
        $secondItem = $this->makeItem(20, 5);

        $response = $this->postJson('/api/v1/orders/calculation', [
            'items' => [
                ['id' => $firstItem->id, 'quantity' => 1],
                ['id' => $secondItem->id, 'quantity' => 1],
            ],
            'metadata' => ['shipOption' => 'delivery'],
        ]);

        $response->assertOk();

        $this->assertEquals(60, $response->json('subtotal'));
        $this->assertEquals(10, $response->json('shipping'));
        $this->assertEquals(70, $response->json('grand_total'));
    }

    public function test_calculation_endpoint_inclusive_tax_breakdown_adds_up(): void
    {
        $this->configureTax(10, true);
        $item = $this->makeItem(40, 10);

        $response = $this->postJson('/api/v1/orders/calculation', [
            'items' => [
                ['id' => $item->id, 'quantity' => 1],
            ],
            'metadata' => ['shipOption' => 'delivery'],
        ]);

        $response->assertOk();

        $this->assertEquals(50, $response->json('grand_total'));
        $this->assertEquals(
            $response->json('grand_total'),
            $response->json('subtotal') + $response->json('shipping') + $response->json('tax')
        );
    }

    public function test_invoice_renders_persisted_shipping_and_grand_total(): void
    {
        $this->configureTax(0, false);
        $item = $this->makeItem(40, 10);

        $order = $this->makeOrder($item, [
            'sub_total' => 4000,
            'shipping_total' => 1000,
            'tax_total' => 0,
            'grand_total' => 5000,
        ]);

        $html = (new Invoice($order))->render();

        $this->assertStringContainsString('$40.00', $html);
        $this->assertStringContainsString('$10.00', $html);
        $this->assertStringContainsString('$50.00', $html);
    }

    public function test_invoice_rebuilds_totals_for_legacy_orders_without_a_breakdown(): void
    {
        $this->configureTax(0, false);
        $item = $this->makeItem(40, 10);

        // Legacy order: only the old total column is populated, and it does not
        // include the shipping charge.
        $order = $this->makeOrder($item, [
            'grand_total' => 4000,
        ]);

        $this->assertFalse($order->hasPersistedTotals());

        $html = (new Invoice($order))->render();

        $this->assertStringContainsString('$40.00', $html);
        $this->assertStringContainsString('$10.00', $html);
        $this->assertStringContainsString('$50.00', $html);
    }

    public function test_invoice_prints_free_shipping_for_pickup_orders(): void
    {
        $this->configureTax(0, false);
        $item = $this->makeItem(40, 10);

        $order = $this->makeOrder($item, [
            'sub_total' => 4000,
            'shipping_total' => 0,
            'tax_total' => 0,
            'grand_total' => 4000,
        ], 'pickup');

        $html = (new Invoice($order))->render();

        $this->assertStringContainsString('FREE', $html);
        $this->assertStringContainsString('$40.00', $html);
    }
}
