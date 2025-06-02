<?php

namespace App\Mail\Orders;

use App\Models\TeamRegistration;
use App\Models\Order;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamRegistrationInvoice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order for this invoice
     *
     * @var TeamRegistration $teamRegistration
     */
    protected TeamRegistration $teamRegistration;

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
    public function __construct(TeamRegistration $teamRegistration, bool $toAdmin = false)
    {
        $this->teamRegistration = $teamRegistration;
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

        $payload = [];
        $payload['target'] = $this->teamRegistration->id;
        $payload['type'] = $this->teamRegistration->item->type;
        $encryptedToken = encrypt($payload);
        $url = env('APP_URL') . '/transaction/?key=' . $encryptedToken;

        return $this
                ->subject('Invoice')
                ->view('mail.registrationInvoice')
                ->with([
                    'url' => $url,
                    'order' => $this->teamRegistration,
                    'taxValue' => $taxValue,
                    'taxToggle' => $toggleTax,
                    'toAdmin' => $this->toAdmin,
                ]);
    }
}
