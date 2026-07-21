<?php

namespace App\Services;

use App\Models\WaitingLounge;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LoungeService
{
    public const MAX_ACTIVE_CHECKOUTS = 5;
    public const WAITING_TTL_SECONDS = 30;
    public const CHECKOUT_TTL_MINUTES = 15;

    public function checkIn(int $seriesId, string $clientId): array
    {
        return DB::transaction(function () use ($seriesId, $clientId) {
            // The payment provider is shared by every event, so admission must
            // be serialized globally rather than once per series.
            $capacityLock = DB::table('waiting_lounge_locks')
                ->where('name', 'global_checkout')
                ->lockForUpdate()
                ->first();

            if (!$capacityLock) {
                throw new RuntimeException('The global checkout capacity lock is missing.');
            }

            $now = now();

            WaitingLounge::query()
                ->where('expires_at', '<=', $now)
                ->delete();

            $entry = WaitingLounge::query()
                ->where('series_id', $seriesId)
                ->where('client_id', $clientId)
                ->first();

            if ($entry && $entry->status === WaitingLounge::STATUS_ACTIVE) {
                return $this->passResponse($entry);
            }

            if (!$entry) {
                $entry = WaitingLounge::create([
                    'series_id' => $seriesId,
                    'client_id' => $clientId,
                    'status' => WaitingLounge::STATUS_WAITING,
                    'expires_at' => $now->copy()->addSeconds(self::WAITING_TTL_SECONDS),
                ]);
            } else {
                // Waiting clients poll every five seconds. This is their heartbeat.
                $entry->update([
                    'expires_at' => $now->copy()->addSeconds(self::WAITING_TTL_SECONDS),
                ]);
            }

            $activeCount = WaitingLounge::query()
                ->where('status', WaitingLounge::STATUS_ACTIVE)
                ->where('expires_at', '>', $now)
                ->count();

            $waitingAhead = WaitingLounge::query()
                ->where('status', WaitingLounge::STATUS_WAITING)
                ->where('id', '<', $entry->id)
                ->count();

            $availableSlots = max(0, self::MAX_ACTIVE_CHECKOUTS - $activeCount);

            if ($availableSlots > 0 && $waitingAhead < $availableSlots) {
                $entry->update([
                    'status' => WaitingLounge::STATUS_ACTIVE,
                    'expires_at' => $now->copy()->addMinutes(self::CHECKOUT_TTL_MINUTES),
                ]);

                return $this->passResponse($entry->fresh());
            }

            return [
                'status' => WaitingLounge::STATUS_WAITING,
                'position' => $waitingAhead + 1,
            ];
        });
    }

    public function hasValidActiveSession(
        string $encryptedToken,
        string $clientId,
        int $seriesId
    ): bool {
        try {
            $token = decrypt($encryptedToken);
        } catch (\Throwable $exception) {
            return false;
        }

        if (!is_array($token)
            || !isset($token['id'], $token['item'], $token['exp'])
            || !hash_equals((string) $token['id'], $clientId)
            || (int) $token['item'] !== $seriesId
            || (int) $token['exp'] <= now()->timestamp) {
            return false;
        }

        return WaitingLounge::query()
            ->where('client_id', $clientId)
            ->where('series_id', $seriesId)
            ->where('status', WaitingLounge::STATUS_ACTIVE)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function stats(int $seriesId): array
    {
        $now = now();
        $active = WaitingLounge::query()
            ->where('status', WaitingLounge::STATUS_ACTIVE)
            ->where('expires_at', '>', $now)
            ->count();
        $waiting = WaitingLounge::query()
            ->where('status', WaitingLounge::STATUS_WAITING)
            ->where('expires_at', '>', $now)
            ->count();

        return [
            'active_shoppers' => $active,
            'queued_shoppers' => $waiting,
            'slots_available' => max(0, self::MAX_ACTIVE_CHECKOUTS - $active),
            'total_limit' => self::MAX_ACTIVE_CHECKOUTS,
        ];
    }

    private function passResponse(WaitingLounge $entry): array
    {
        return [
            'status' => 'pass',
            'token' => encrypt([
                'id' => $entry->client_id,
                'item' => $entry->series_id,
                'exp' => $entry->expires_at->timestamp,
            ]),
        ];
    }
}
