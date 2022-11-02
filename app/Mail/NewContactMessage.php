<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    
    /**
     * Guest Name
     * 
     * @var string $name
     */
    protected string $name;
    
    /**
     * Guest Email
     * 
     * @var string $email
     */
    protected string $email;
    
    /**
     * Guest Message
     * 
     * @var string $message
     */
    protected string $message;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $name, string $email, string $message)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
                ->subject('Invoice')
                ->with([
                    'name' => $this->name,
                    'email' => $this->email,
                    'guestMessage' => $this->message,
                ])
                ->view('mail.new-contact-message');
    }
}
