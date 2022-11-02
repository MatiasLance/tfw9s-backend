<?php

namespace App\Modules\Mail;

use App\Models\Order;

interface MailServiceInterface
{
    /**
     * Send the customer and the admin a copy of the invoice of the transaction
     * 
     * @param Order $order
     */
    public function sendInvoice(Order $order);

    /**
     * Send the contents of the contact form to the admins
     * 
     * @param array $data The data from the contact form
     */
    public function sendContactForm(array $data);

    /**
     * Send the user the link for reseting their password
     * 
     * @param array $data
     * @param string $userEmail
     */
    public function sendPasswordResetLink(array $data, string $userEmail);
}