<?php

namespace App\Modules\Order;

/**
 * Builds a single, consistent breakdown for merchandise orders.
 *
 * All amounts are in cents. Shipping is the amount actually charged to the
 * customer. The returned values always satisfy:
 *
 *     grand_total = sub_total + shipping_total + tax_total
 */
class OrderTotals
{
    /**
     * Calculate an order's subtotal, shipping, tax, and grand total.
     *
     * @param  int  $productTotal  Product total in cents (already discounted).
     * @param  int  $shippingTotal  Shipping charge in cents.
     * @param  float  $taxRate  Tax rate as a percentage (for example 10 for 10%).
     * @param  bool  $taxInclusive  Whether product prices already include tax.
     * @return array{sub_total:int, shipping_total:int, tax_total:int, grand_total:int}
     */
    public static function calculate(
        int $productTotal,
        int $shippingTotal,
        float $taxRate = 0.0,
        bool $taxInclusive = false
    ): array {
        $productTotal = max(0, $productTotal);
        $shippingTotal = max(0, $shippingTotal);
        $taxRate = max(0.0, $taxRate);

        if ($taxInclusive) {
            // Product prices already include tax, but the shipping charge is
            // presented as a separate line item. The tax line therefore only
            // covers the product portion; tax embedded in shipping remains
            // part of the shipping line.
            $subTotal = $taxRate > 0
                ? (int) round($productTotal / (1 + ($taxRate / 100)))
                : $productTotal;

            $grandTotal = $productTotal + $shippingTotal;
            $taxTotal = max(0, $grandTotal - $subTotal - $shippingTotal);
        } else {
            $subTotal = $productTotal;
            $taxTotal = (int) round(($productTotal + $shippingTotal) * ($taxRate / 100));
            $grandTotal = $subTotal + $shippingTotal + $taxTotal;
        }

        return [
            'sub_total' => $subTotal,
            'shipping_total' => $shippingTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Shipping charge for a cart, in cents.
     *
     * Mirrors the cart calculation: delivery orders pay the summed shipping
     * charge of every item, but a cart containing an item that cannot be
     * shipped (for example an event pickup only item) pays no shipping.
     *
     * @param  iterable<int, array<string, mixed>>  $items  Each item needs
     *         "has_shipping" and (optionally) "shipping_charge".
     */
    public static function shippingTotal(iterable $items, ?string $shipOption): int
    {
        if ($shipOption !== 'delivery') {
            return 0;
        }

        $items = collect($items);

        if ($items->contains(fn (array $item): bool => ! ($item['has_shipping'] ?? false))) {
            return 0;
        }

        return (int) round($items->sum(
            fn (array $item): float => (float) ($item['shipping_charge'] ?? 0)
        ) * 100);
    }

    /**
     * Shipping charge for an existing order when the order predates the
     * persisted totals columns.
     *
     * Legacy orders may have a null ship option because older checkout
     * clients did not always send one, so any non-pickup order is treated as
     * a delivery order for display purposes.
     */
    public static function legacyShippingTotal(iterable $lineItems, ?string $shipOption): int
    {
        if ($shipOption === 'pickup') {
            return 0;
        }

        $lineItems = collect($lineItems);

        if ($lineItems->isEmpty()) {
            return 0;
        }

        $allShippable = $lineItems->every(
            fn ($lineItem): bool => (bool) optional($lineItem->item)->has_shipping
        );

        if (! $allShippable) {
            return 0;
        }

        return (int) round($lineItems->sum(
            fn ($lineItem): float => (float) (optional($lineItem->item)->shipping_charge ?? 0)
        ) * 100);
    }
}
