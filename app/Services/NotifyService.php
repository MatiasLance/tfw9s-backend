<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyService
{
    public function sendNotification(array $data)
    {
        $url = env('SOCKET_URL', 'http://socket:3001') . '/notify';
        
        Http::post($url, [
            'event' => 'registration-form-status',
            'payload' => [
                'success' => $data["success"],
                'message' => $data["message"],
                'data' => $data["data"],
            ]
        ]);
    }

    public function sendNotificationForLoungeStatus(array $data)
    {
        $url = env('SOCKET_URL', 'http://socket:3001') . '/lounge-status';
        
        Http::post($url, [
            'event' => 'check-lounge-status',
            'payload' => [
                'active_shoppers' => $data['active_shoppers'],
                'queued_shoppers' => $data['queued_shoppers'],
                'slots_available' => $data['slots_available'],
                'total_limit' => $data['total_limit']
            ]
        ]);
    }

    /**
     * Broadcast a small invalidation event. Each browser then reloads its own
     * current search/filter/page instead of receiving another user's result set.
     */
    public function sendItemChangedNotification(int $itemId, string $action, ?bool $isActive = null): void
    {
        $url = env('SOCKET_URL', 'http://socket:3001') . '/item-list';

        $payload = [
            'item_id' => $itemId,
            'action' => $action,
        ];

        if ($isActive !== null) {
            $payload['is_active'] = $isActive;
        }

        try {
            Http::timeout(2)->post($url, [
                'event' => 'item-list-changed',
                'payload' => $payload,
            ]);
        } catch (Throwable $exception) {
            // A socket outage must not roll back or fail a successful item edit.
            Log::warning('Unable to broadcast item list change.', [
                'item_id' => $itemId,
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
