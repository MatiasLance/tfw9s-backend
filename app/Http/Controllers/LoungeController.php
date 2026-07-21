<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotifyService;
use App\Services\LoungeService;

class LoungeController extends Controller
{
    protected $notifyService;
    protected $loungeService;

    public function __construct(
        NotifyService $notifyService,
        LoungeService $loungeService
    )
    {
        $this->notifyService = $notifyService;
        $this->loungeService = $loungeService;
    }

    public function checkQueue(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|integer|exists:series,id',
            'client_id' => 'required|string|max:255',
        ]);

        return response()->json($this->loungeService->checkIn(
            (int) $validated['item'],
            $validated['client_id']
        ));
    }

    public function getLiveStats($itemId)
    {
        $data = $this->loungeService->stats((int) $itemId);

        $this->notifyService->sendNotificationForLoungeStatus($data);

        return response()->json($data);
    }
}
