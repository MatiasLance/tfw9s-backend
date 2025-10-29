<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSMSCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sms {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMS sending for a team';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $teamId = $this->argument('team_id');
        
        $this->info("Testing SMS for team ID: {$teamId}");
        
        $request = new \Illuminate\Http\Request(['id' => $teamId]);
        
        $controller = app()->make(\App\Http\Controllers\SMSController::class);
        $response = $controller->sendLinkViaSMS($request);
        
        $this->info("Response: " . $response->getContent());

        return Command::SUCCESS;
    }
}
