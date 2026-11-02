<?php

namespace App\Models;

use App\Modules\Order\Traits\HasShippingOptions;
use App\Modules\Payment\PaymentGateway;
use App\Models\Item;
use App\Models\OrderLineItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use HasShippingOptions;

    protected $casts = [
        'payment_gateway' => PaymentGateway::class,
        'is_verified' => 'boolean',
        'total' => 'integer',
        'sub_total' => 'integer',
        'shipping_total' => 'integer',
        'tax_total' => 'integer',
    ];

    public function getOrderNumberAttribute()
    {
        return sprintf('%03d', $this->id);
    }

    public function getCustomerFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getShippingNoteAttribute()
    {
        return $this->getShippingNote($this->shipping_type);
    }

    public function getProductSubTotalAttribute()
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item->value * $item->quantity;
        }
        return $subtotal;
    }

    /**
     * Product total in cents, independent of the persisted sub_total column.
     */
    public function getProductTotalInCentsAttribute(): int
    {
        return (int) $this->items->sum(
            fn (OrderLineItem $item): int => (int) $item->price * (int) $item->quantity
        );
    }

    /**
     * Whether this order has a persisted totals breakdown.
     */
    public function hasPersistedTotals(): bool
    {
        return ! is_null($this->sub_total)
            && ! is_null($this->shipping_total)
            && ! is_null($this->tax_total);
    }

    public function getTotalGSTAttribute()
    {
        $totalGST = 0;
        foreach ($this->items as $item) {
            $totalGST += $item->gst * $item->quantity;
        }
        return $totalGST;
    }

    public function items()
    {
        return $this->hasMany(OrderLineItem::class);
    }

    public function orderItems()
    {
        return $this->hasManyThrough(
            Item::class,
            OrderLineItem::class,
            'order_id',
            'id',
            'id',
            'item_id'
        );
    }
}
