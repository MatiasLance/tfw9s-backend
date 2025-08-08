<?php

namespace App\Modules\Mail;

use App\Mail\NewContactMessage;
use App\Mail\Orders\Invoice;
use App\Mail\Orders\IndivRegistrationInvoice;
use App\Mail\Orders\TeamRegistrationInvoice;
use App\Mail\PasswordResetLink;
use App\Models\Order;
use App\Models\IndividualRegistration;
use App\Models\TeamRegistration;
use GuzzleHttp\Client;
use App\Mail\RegistrationLink;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService implements MailServiceInterface
{
    public function sendInvoice(Order $order)
    {
        $mail = new Invoice($order);
        $content = $mail->render();

        $adminSubject = 'You have a new order on tfw9s.com.au!';
        $customerSubject = 'Here is the invoice for your recent order on tfw9s.com.au';

        $this->send([env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au')], $adminSubject, $content);
        $this->send([$order->email], $customerSubject, $content);
    }

    public function sendIndividualRegistrationInvoice(IndividualRegistration $individualRegistration)
    {
        $mail = new IndivRegistrationInvoice($individualRegistration);
        $content = $mail->render();

        $adminSubject = 'You have a new order on tfw9s.com.au! - INVOICE #: ' . $individualRegistration->id;
        $customerSubject = 'Here is the invoice for your recent payment on tfw9s.com.au - INVOICE #: ' . $individualRegistration->id;

        $this->send([env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au')], $adminSubject, $content);
        $this->send([$individualRegistration->email], $customerSubject, $content);
    }

    public function sendTeamRegistrationInvoice(TeamRegistration $teamRegistration)
    {
        $mail = new TeamRegistrationInvoice($teamRegistration);
        $content = $mail->render();

        $adminSubject = 'You have a new order on tfw9s.com.au! - INVOICE #: ' . $teamRegistration->id;
        $customerSubject = 'Here is the invoice for your recent payment on tfw9s.com.au - INVOICE #: ' . $teamRegistration->id;

        $this->send([env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au')], $adminSubject, $content);
        $this->send([$teamRegistration->coach_email], $customerSubject, $content);
        $this->send([$teamRegistration->manager_email], $customerSubject, $content);
    }

    public function sendContactForm(array $data)
    {
        $to = [
            env('ADMIN_EMAIL_ADDRESS', 'admin@tfw9s.com.au')
        ];

        $subject = 'You have a new message from ' . $data['name'] . ' via tfw9s.com.au';

        $mail = new NewContactMessage($data['name'], $data['email'], $data['message']);
        $content = $mail->render();

        $this->send($to, $subject, $content);
    }

    public function sendPasswordResetLink(array $data, string $userEmail)
    {
        $mail = new PasswordResetLink($data['resetLink']);
        $content = $mail->render();

        $subject = 'Password Reset Link';

        $this->send([$userEmail], $subject, $content);
    }

    public function sendCoachSeriesNotification(string $coachEmail, string $seriesName, string $link, string $coach, string $code)
    {
        $mail = new RegistrationLink($seriesName, $link, $coach, $code);
        $content = $mail->render();

        $subject = 'New Series Created: ' . $seriesName;

        $this->send([$coachEmail], $subject, $content);
    }

    /**
     * Sends the mail to the email relay server.
     *
     * @param array $to The recipients of the email
     * @param string $subject
     * @param string $content
     *
     * @return bool
     */
    protected function send(array $to, string $subject, string $content): bool
    {
        $guzzle = new Client();

        try {

            $response = $guzzle->request('POST', 'http://'. env('SMTP_RELAY_HOST') .'/api/v1/mail/send', [
                'form_params' => [
                    'from' => 'noreply@smtprelays.com',
                    'recipients' => $to,
                    'cc' => [],
                    'bcc' => [],
                    'subject' => $subject,
                    'content' => $content,
                    'attachments' => [],
                ],
            ]);

        $statusCode = $response->getStatusCode() === 200;

        } catch (\GuzzleHttp\Exception\ServiceException $e) {
            dd(\GuzzleHttp\Psr7\Message::toString($e->getResponse()));
        }

        return $statusCode;
    }
}
