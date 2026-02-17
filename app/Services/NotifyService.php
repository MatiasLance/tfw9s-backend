<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
                'slots_available' => $data['slots_available'],
                'total_limit' => $data['total_limit']
            ]
        ]);
    }
}