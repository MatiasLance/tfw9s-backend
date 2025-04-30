<?php

namespace App\Modules\Mail;

use App\Models\Order;
use App\Models\IndividualRegistration;
use App\Models\TeamRegistration;

interface MailServiceInterface
{
    /**
     * Send the customer and the admin a copy of the invoice of the transaction
     *
     * @param Order $order
     */
    public function sendInvoice(Order $order);

    /**
     * Send the customer and the admin a copy of the invoice of the transaction
     *
     * @param IndividualRegistration $inidividualRegistration
     */
    public function sendIndividualRegistrationInvoice(IndividualRegistration $inidividualRegistration);

    /**
     * Send the customer and the admin a copy of the invoice of the transaction
     *
     * @param TeamRegistration $teamRegistration
     */
    public function sendTeamRegistrationInvoice(TeamRegistration $teamRegistration);

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

    /**
     * Send the coach a notification about the series
     *
     * @param string $seriesName
     * @param string $link
     */
     public function sendCoachSeriesNotification(string $coachEmail, string $seriesName, string $link);
}
