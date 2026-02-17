<?php

namespace App\Jobs;

use App\Models\TeamRegistration;
use App\Modules\Mail\MailServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTeamRegistrationInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
   
    public $registration;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TeamRegistration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MailServiceInterface $mailService)
    {
        $mailService->sendTeamRegistrationInvoice($this->registration);
    }
}
