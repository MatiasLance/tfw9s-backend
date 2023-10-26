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
        $tax = Tax::firstOrFail();
        $toggleTax = ToggleTaxControl::firstOrFail();

        $taxValue = $toggleTax->toggleControl1 ? $tax->addTaxValue : $tax->includeTaxValue;
        
        return $this
                ->subject('Invoice')
                ->view('mail.invoice')
                ->with([
                    'order' => $this->order,
                    'taxValue' => $taxValue,
                    'taxToggle' => $toggleTax,
                    'toAdmin' => $this->toAdmin,
                ]);
    }
}
