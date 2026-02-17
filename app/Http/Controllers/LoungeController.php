<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WaitingLounge;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\NotifyService;

class LoungeController extends Controller
{
    const MAX_ACTIVE_CHECKOUTS = 5; 

    protected $notifyService;

    public function __construct(NotifyService $notifyService)
    {
        $this->notifyService = $notifyService;
    }

    public function checkQueue(Request $request)
    {
        $itemId = $request->item;
        $clientId = $request->client_id;
        $now = Carbon::now();

        return DB::transaction(function () use ($itemId, $clientId, $now) {
            
            WaitingLounge::where('series_id', $itemId)
                ->where('expires_at', '<', $now)
                ->delete();

            $userEntry = WaitingLounge::updateOrCreate(
                ['client_id' => $clientId, 'series_id' => $itemId],
                ['expires_at' => $now->copy()->addSeconds(30)]
            );

            $rank = WaitingLounge::where('series_id', $itemId)
                ->where('id', '<', $userEntry->id)
                ->count();

            if ($rank < self::MAX_ACTIVE_CHECKOUTS) {
                return response()->json([
                    'status' => 'pass',
                    'token' => $this->generateToken($clientId, $itemId)
                ]);
            }

            return response()->json([
                'status' => 'waiting',
                'position' => $rank - self::MAX_ACTIVE_CHECKOUTS + 1
            ]);
        });
    }

    private function generateToken($clientId, $itemId)
    {
        return encrypt([
            'id' => $clientId,
            'item' => $itemId,
            'exp' => Carbon::now()->addMinutes(15)->timestamp
        ]);
    }

    public function getLiveStats($itemId)
    {
        $activeInLounge = WaitingLounge::where('series_id', $itemId)
            ->where('expires_at', '>', now())
            ->count();
        
        $data = [
            'active_shoppers' => $activeInLounge,
            'slots_available' => max(0, self::MAX_ACTIVE_CHECKOUTS - $activeInLounge),
            'total_limit' => self::MAX_ACTIVE_CHECKOUTS
        ];

        $this->notifyService->sendNotificationForLoungeStatus($data);

        return response()->json([
            'active_shoppers' => $activeInLounge,
            'slots_available' => max(0, self::MAX_ACTIVE_CHECKOUTS - $activeInLounge),
            'total_limit' => self::MAX_ACTIVE_CHECKOUTS
        ]);
    }
}