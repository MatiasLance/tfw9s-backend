<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Series;
use App\Services\SMSNotificationService;

class SMSController extends Controller
{

    protected $smsNotificationService;

    public function __construct(SMSNotificationService $smsNotificationService)
    {
        $this->smsNotificationService = $smsNotificationService;
    }

    public function sendLinkViaSMS(Request $request) 
    {
        $id = (int) $request->input('id');
        $team = Team::withTrashed()->findOrFail($id);
        $series = Series::withTrashed()->findOrFail($team->series_id);
        
        $smsResponse = $this->smsNotificationService->sendLink($team, $series);

        return $smsResponse;
    }
}