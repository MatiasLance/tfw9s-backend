<?php

namespace App\Mail\Orders;

use App\Models\Order;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invoice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order for this invoice
     *
     * @var Order $order
     */
    protected Order $order;

    /**
     * Is the email to be sent to admin
     *
     * @var bool $toAdmin
     */
    protected bool $toAdmin;

    protected function getTax()
    {
        $tax = Tax::latest()->first();
        return $tax;
    }

    protected function getToggleControl()
    {
        $toggleTaxControl = ToggleTaxControl::latest()->first();
        return $toggleTaxControl;
    }

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, bool $toAdmin = false)
    {
        $this->order = $order;
        $this->toAdmin = $toAdmin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->order->load(['items.item.sizeVariants', 'items.selectedVariant']);

        return $this
                ->subject('Invoice')
                ->view('mail.invoice')
                ->with([
                    'order' => $this->order,
                    'tax' => $this->getTax(),
                    'tax_toggle_control' => $this->getToggleControl(),
                    'to_admin' => $this->toAdmin,
                ]);
    }
}
