<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Order;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use App\Modules\Order\OrderTotals;
use App\Modules\Payment\PaymentGateway;
use Illuminate\Database\Seeder;

/**
 * Creates a handful of demo merchandise orders for checking order totals and
 * invoice rendering.
 *
 * Run it with:
 *
 *     php artisan db:seed --class=OrderTotalsDemoSeeder
 *
 * The seeder is safe to run repeatedly: items and orders are matched by a
 * stable name/transaction id and updated in place.
 */
class OrderTotalsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tax = Tax::latest()->first();
        $toggle = ToggleTaxControl::latest()->first();

        $taxRate = (float) ($tax?->getAddTaxValue() ?? 0);
        $inclusive = (bool) ($toggle?->isToggleControle2() ?? false);

        $item = Item::firstOrNew([
            'name' => 'Demo Tee (shipping demo)',
        ]);

        $item->description = '<p>Demo product used to verify order totals and invoices.</p>';
        $item->price = 40;             // Accessor stores this as 4000 cents.
        $item->saleprice = 0;
        $item->is_on_sale = false;
        $item->stock = 100;
        $item->has_shipping = true;
        $item->shipping_charge = 10;   // Dollars, as stored on items.
        $item->is_featured = false;
        $item->show_rrp = false;
        $item->save();

        $productTotal = $item->centPrice();                 // 4000 cents.
        $shippingTotal = (int) round($item->shipping_charge * 100); // 1000 cents.
        $deliveryTotals = OrderTotals::calculate($productTotal, $shippingTotal, $taxRate, $inclusive);
        $pickupTotals = OrderTotals::calculate($productTotal, 0, $taxRate, $inclusive);

        $this->createOrder(
            'demo-order-fixed-delivery',
            $item,
            $deliveryTotals,
            'delivery'
        );

        $this->createOrder(
            'demo-order-fixed-pickup',
            $item,
            $pickupTotals,
            'pickup'
        );

        // Reproduces the reported bug: an old order whose recorded total only
        // covers the product, with no persisted breakdown. The invoice should
        // rebuild the breakdown and show product + shipping.
        $this->createOrder(
            'demo-order-legacy-delivery',
            $item,
            null,
            'delivery',
            $productTotal
        );

        $this->command?->info('Demo orders created for item #' . $item->id . ' ($' . number_format($productTotal / 100, 2) . ' + $' . number_format($shippingTotal / 100, 2) . ' shipping):');
        $this->command?->info('  demo-order-fixed-delivery   -> grand total $' . number_format($deliveryTotals['grand_total'] / 100, 2));
        $this->command?->info('  demo-order-fixed-pickup     -> grand total $' . number_format($pickupTotals['grand_total'] / 100, 2));
        $this->command?->info('  demo-order-legacy-delivery  -> recorded total $' . number_format($productTotal / 100, 2) . ' (no breakdown; invoice rebuilds it)');
    }

    /**
     * @param  array{sub_total:int, shipping_total:int, tax_total:int, grand_total:int}|null  $totals
     */
    private function createOrder(
        string $transactionId,
        Item $item,
        ?array $totals,
        string $shipOption,
        ?int $recordedTotal = null
    ): Order {
        $order = Order::firstOrNew([
            'transaction_id' => $transactionId,
        ]);

        $order->payment_gateway = PaymentGateway::STRIPE;
        $order->firstname = 'Demo';
        $order->lastname = 'Customer';
        $order->phone_number = '0400000000';
        $order->email = 'demo@example.test';
        $order->ship_option = $shipOption;
        $order->address = $shipOption === 'delivery' ? '1 Demo Street' : null;
        $order->post_code = $shipOption === 'delivery' ? '2250' : null;
        $order->remarks = 'Seeded order used to verify totals and invoice rendering.';
        $order->total = $totals['grand_total'] ?? $recordedTotal ?? 0;
        $order->sub_total = $totals['sub_total'] ?? null;
        $order->shipping_total = $totals['shipping_total'] ?? null;
        $order->tax_total = $totals['tax_total'] ?? null;
        $order->is_verified = true;
        $order->save();

        $order->items()->updateOrCreate(
            ['item_id' => $item->id],
            [
                'price' => $item->centPrice(),
                'quantity' => 1,
                'selected_color' => null,
            ]
        );

        return $order;
    }
}
