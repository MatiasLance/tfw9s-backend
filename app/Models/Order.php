<?php

namespace App\Models;

use App\Modules\Order\Traits\HasShippingOptions;
use App\Modules\Payment\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use HasShippingOptions;

    protected $casts = [
        'payment_gateway' => PaymentGateway::class,
        'is_verified' => 'boolean'
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

    public function getSubTotalAttribute()
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item->value * $item->quantity;
        }
        return $subtotal;
    }

    public function getTotalGSTAttribute()
    {
        $totalGST = 0;
        foreach ($this->items as $item) {
            $totalGST += $item->gst * $item->quantity;
        }
        return $totalGST;
    }

    // public function getTotalAttribute()
    // {
    //     $total = 0;
    //     foreach ($this->items as $item) {
    //         $total += $item->total;
    //     }
    //     return $total;
    // }

    public function items()
    {
        return $this->hasMany(OrderLineItem::class);
    }
}
