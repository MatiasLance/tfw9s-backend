<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationLink extends Mailable
{
    use Queueable, SerializesModels;

    protected string $seriesName;

    protected string $link;

    protected string $coach;

    protected string $discountCode;

    public function __construct(string $seriesName, string $link, string $coach, string $discountCode)
    {
        $this->seriesName = $seriesName;
        $this->link = $link;
        $this->coach = $coach;
        $this->discountCode = $discountCode;
    }

    public function build()
    {
        return $this
            ->subject('New Series Created: ' . $this->seriesName)
            ->with([
                'seriesName' => $this->seriesName,
                'link' => $this->link,
                'coach' => $this->coach,
                'code' => $this->discountCode
            ])
            ->view('mail.coach-series-notification');
    }
}

