<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSMSConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:smsconn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sms connection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controller = app()->make(\App\Http\Controllers\SMSController::class);
        $response = $controller->testTwilioConnection();
        
        $this->info("Response: " . $response->getContent());
        return Command::SUCCESS;
    }
}
