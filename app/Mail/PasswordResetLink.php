<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetLink extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Reset link
     * 
     * @var string $resetLink
     */
    protected string $resetLink;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $resetLink)
    {
        $this->resetLink = $resetLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
                ->subject('Password Reset Link')
                ->with([
                    'resetUrl' => $this->resetLink
                ])
                ->view('mail.password-reset');
    }
}
