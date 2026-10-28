<?php

namespace App\Modules\Order;

use App\Models\ShippingOptions;

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
     * Current delivery and pickup charges, in cents.
     *
     * Configuration lives in the single shipping_options row so the admin
     * can change the fees without a deployment.
     *
     * @return array{delivery:int, pickup:int}
     */
    public static function configuredCharges(): array
    {
        try {
            $options = ShippingOptions::query()->first();
        } catch (\Throwable $exception) {
            $options = null;
        }

        return [
            'delivery' => self::normaliseCharge(
                $options?->delivery_charge,
                ShippingOptions::DEFAULT_DELIVERY_CHARGE
            ),
            'pickup' => self::normaliseCharge(
                $options?->pickup_charge,
                ShippingOptions::DEFAULT_PICKUP_CHARGE
            ),
        ];
    }

    /**
     * Shipping charge for a cart, in cents.
     *
     * Delivery orders pay the configured delivery fee as long as every item
     * can be shipped. Pickup orders pay the configured pickup fee (which is
     * normally zero). Items that cannot be shipped make a delivery order
     * ineligible for shipping, matching the previous cart behaviour.
     *
     * @param  iterable<int, array<string, mixed>>  $items  Each item needs
     *         "has_shipping".
     */
    public static function shippingTotal(
        iterable $items,
        ?string $shipOption,
        ?int $deliveryCharge = null,
        ?int $pickupCharge = null
    ): int {
        if ($shipOption !== 'delivery' && $shipOption !== 'pickup') {
            return 0;
        }

        if ($shipOption === 'delivery') {
            $items = collect($items);

            if ($items->contains(fn (array $item): bool => ! ($item['has_shipping'] ?? false))) {
                return 0;
            }
        }

        $charges = self::resolveCharges($deliveryCharge, $pickupCharge);

        return $shipOption === 'pickup' ? $charges['pickup'] : $charges['delivery'];
    }

    /**
     * Shipping charge for an existing order when the order predates the
     * persisted totals columns.
     *
     * Legacy orders may have a null ship option because older checkout
     * clients did not always send one, so any non-pickup order is treated as
     * a delivery order for display purposes.
     */
    public static function legacyShippingTotal(
        iterable $lineItems,
        ?string $shipOption,
        ?int $deliveryCharge = null,
        ?int $pickupCharge = null
    ): int {
        $lineItems = collect($lineItems);

        if ($lineItems->isEmpty()) {
            return 0;
        }

        if ($shipOption === 'pickup') {
            return self::resolveCharges($deliveryCharge, $pickupCharge)['pickup'];
        }

        $allShippable = $lineItems->every(
            fn ($lineItem): bool => (bool) optional($lineItem->item)->has_shipping
        );

        if (! $allShippable) {
            return 0;
        }

        return self::resolveCharges($deliveryCharge, $pickupCharge)['delivery'];
    }

    /**
     * Resolve the configured charges, falling back to the current defaults.
     *
     * Callers that already loaded the shipping options can pass the values
     * directly to avoid an extra query.
     *
     * @return array{delivery:int, pickup:int}
     */
    private static function resolveCharges(?int $deliveryCharge, ?int $pickupCharge): array
    {
        if (! is_null($deliveryCharge) && ! is_null($pickupCharge)) {
            return [
                'delivery' => self::normaliseCharge(
                    $deliveryCharge,
                    ShippingOptions::DEFAULT_DELIVERY_CHARGE
                ),
                'pickup' => self::normaliseCharge(
                    $pickupCharge,
                    ShippingOptions::DEFAULT_PICKUP_CHARGE
                ),
            ];
        }

        $configured = self::configuredCharges();

        return [
            'delivery' => self::normaliseCharge(
                $deliveryCharge,
                $configured['delivery']
            ),
            'pickup' => self::normaliseCharge(
                $pickupCharge,
                $configured['pickup']
            ),
        ];
    }

    private static function normaliseCharge(?int $charge, int $default): int
    {
        if (is_null($charge)) {
            return max(0, $default);
        }

        return max(0, $charge);
    }
}
