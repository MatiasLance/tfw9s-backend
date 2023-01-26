<?php

namespace App\Modules\Mail;

use App\Mail\NewContactMessage;
use App\Mail\Orders\Invoice;
use App\Mail\PasswordResetLink;
use App\Models\Order;
use GuzzleHttp\Client;

class MailService implements MailServiceInterface
{
    public function sendInvoice(Order $order)
    {
        $mail = new Invoice($order);
        $content = $mail->render();

        $adminSubject = 'You have a new order on thedrumhq.com.au!';
        $customerSubject = 'Here is the invoice for your recent order on thedrumhq.com.au';

        $this->send([env('ADMIN_EMAIL_ADDRESS', 'tech1.sumomedia@gmail.com')], $adminSubject, $content);
        $this->send([$order->email], $customerSubject, $content);
    }

    public function sendContactForm(array $data)
    {
        $to = [
            env('ADMIN_EMAIL_ADDRESS', 'tech1.sumomedia@gmail.com')
        ];

        $subject = 'You have a new message from ' . $data['name'] . ' via thedrumhq.com.au';
        
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

        $response = $guzzle->request('POST', 'http://'. env('SMTP_RELAY_HOST') .'/api/v1/mail/send', [
            'form_params' => [
                'from' => 'noreply@revamp.pageone247.com',
                'recipients' => $to,
                'cc' => [],
                'bcc' => [],
                'subject' => $subject,
                'content' => $content,
                'attachments' => [],
            ],
        ]);

        return $response->getStatusCode() === 200;
    }
}
