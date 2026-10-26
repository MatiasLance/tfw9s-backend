<?php

namespace App\Modules\Order\Tests;

use App\Modules\Order\OrderTotals;
use Tests\TestCase;

class OrderTotalsTest extends TestCase
{
    public function test_grand_total_includes_shipping_when_tax_is_zero(): void
    {
        $totals = OrderTotals::calculate(4000, 1000, 0.0, false);

        $this->assertSame(4000, $totals['sub_total']);
        $this->assertSame(1000, $totals['shipping_total']);
        $this->assertSame(0, $totals['tax_total']);
        $this->assertSame(5000, $totals['grand_total']);
    }

    public function test_inclusive_tax_breakdown_adds_up_to_grand_total(): void
    {
        $totals = OrderTotals::calculate(4000, 1000, 10.0, true);

        $this->assertSame(5000, $totals['grand_total']);
        $this->assertSame(
            $totals['grand_total'],
            $totals['sub_total'] + $totals['shipping_total'] + $totals['tax_total']
        );
    }

    public function test_exclusive_tax_breakdown_adds_up_to_grand_total(): void
    {
        $totals = OrderTotals::calculate(4000, 1000, 10.0, false);

        $this->assertSame(4000, $totals['sub_total']);
        $this->assertSame(1000, $totals['shipping_total']);
        $this->assertSame(500, $totals['tax_total']);
        $this->assertSame(5500, $totals['grand_total']);
        $this->assertSame(
            $totals['grand_total'],
            $totals['sub_total'] + $totals['shipping_total'] + $totals['tax_total']
        );
    }

    public function test_shipping_is_not_charged_for_pickup(): void
    {
        $shipping = OrderTotals::shippingTotal([
            ['has_shipping' => true, 'shipping_charge' => 10],
        ], 'pickup');

        $this->assertSame(0, $shipping);
    }

    public function test_shipping_is_not_charged_when_the_cart_contains_an_unshippable_item(): void
    {
        $shipping = OrderTotals::shippingTotal([
            ['has_shipping' => true, 'shipping_charge' => 10],
            ['has_shipping' => false, 'shipping_charge' => 0],
        ], 'delivery');

        $this->assertSame(0, $shipping);
    }

    public function test_shipping_charge_is_summed_across_shippable_items(): void
    {
        $shipping = OrderTotals::shippingTotal([
            ['has_shipping' => true, 'shipping_charge' => 10],
            ['has_shipping' => true, 'shipping_charge' => 15.5],
        ], 'delivery');

        $this->assertSame(2550, $shipping);
    }
}
