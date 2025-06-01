<?php

namespace App\Mail\Orders;

use App\Models\IndividualRegistration;
use App\Models\Tax;
use App\Models\ToggleTaxControl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IndivRegistrationInvoice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order for this invoice
     *
     * @var IndividualRegistration $individualRegistration
     */
    protected IndividualRegistration $individualRegistration;

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
    public function __construct(IndividualRegistration $individualRegistration, bool $toAdmin = false)
    {
        $this->individualRegistration = $individualRegistration;
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
        $payload['target'] = $this->individualRegistration->id;
        $payload['type'] = $this->individualRegistration->item->type;
        $encryptedToken = encrypt($payload);
        $url = env('APP_URL') . '/transaction/?key=' . $encryptedToken;

        return $this
                ->subject('Invoice')
                ->view('mail.registrationInvoice')
                ->with([
                    'url' => $url,
                    'order' => $this->individualRegistration,
                    'taxValue' => $taxValue,
                    'taxToggle' => $toggleTax,
                    'toAdmin' => $this->toAdmin,
                ]);
    }
}