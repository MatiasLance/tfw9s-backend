<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $seriesName,
        public string $link
    ) {}

    public function build()
    {
        return $this
            ->subject('New Series Created: ' . $this->seriesName)
            ->view('mail.coach-series-notification');
    }
}

