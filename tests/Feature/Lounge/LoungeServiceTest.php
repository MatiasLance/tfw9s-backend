<?php

namespace Tests\Feature\Lounge;

use App\Models\WaitingLounge;
use App\Services\LoungeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoungeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_only_five_clients_are_admitted_to_checkout(): void
    {
        Carbon::setTestNow('2026-07-20 19:00:00');
        $seriesId = $this->createSeries();
        $service = app(LoungeService::class);

        for ($client = 1; $client <= LoungeService::MAX_ACTIVE_CHECKOUTS; $client++) {
            $response = $service->checkIn($seriesId, "client-{$client}");
            $this->assertSame('pass', $response['status']);
        }

        $waiting = $service->checkIn($seriesId, 'client-6');

        $this->assertSame('waiting', $waiting['status']);
        $this->assertSame(1, $waiting['position']);
        $this->assertSame(
            LoungeService::MAX_ACTIVE_CHECKOUTS,
            WaitingLounge::where('status', WaitingLounge::STATUS_ACTIVE)->count()
        );
    }

    public function test_active_slots_do_not_expire_after_the_waiting_heartbeat_window(): void
    {
        Carbon::setTestNow('2026-07-20 19:00:00');
        $seriesId = $this->createSeries();
        $service = app(LoungeService::class);

        for ($client = 1; $client <= LoungeService::MAX_ACTIVE_CHECKOUTS; $client++) {
            $service->checkIn($seriesId, "client-{$client}");
        }

        Carbon::setTestNow('2026-07-20 19:00:31');
        $waiting = $service->checkIn($seriesId, 'client-6');

        $this->assertSame('waiting', $waiting['status']);
        $this->assertSame(
            LoungeService::MAX_ACTIVE_CHECKOUTS,
            WaitingLounge::where('status', WaitingLounge::STATUS_ACTIVE)->count()
        );
    }

    public function test_checkout_capacity_is_shared_across_series(): void
    {
        Carbon::setTestNow('2026-07-20 19:00:00');
        $divisionOne = $this->createSeries('Division 1');
        $divisionTwo = $this->createSeries('Division 2');
        $service = app(LoungeService::class);

        for ($client = 1; $client <= LoungeService::MAX_ACTIVE_CHECKOUTS; $client++) {
            $seriesId = $client % 2 === 0 ? $divisionTwo : $divisionOne;
            $response = $service->checkIn($seriesId, "global-client-{$client}");
            $this->assertSame('pass', $response['status']);
        }

        $waiting = $service->checkIn($divisionTwo, 'global-client-6');

        $this->assertSame('waiting', $waiting['status']);
        $this->assertSame(1, $waiting['position']);
        $this->assertSame(
            LoungeService::MAX_ACTIVE_CHECKOUTS,
            WaitingLounge::where('status', WaitingLounge::STATUS_ACTIVE)->count()
        );
    }

    public function test_checkout_token_is_bound_to_the_active_client_and_series(): void
    {
        Carbon::setTestNow('2026-07-20 19:00:00');
        $seriesId = $this->createSeries();
        $service = app(LoungeService::class);
        $response = $service->checkIn($seriesId, 'client-1');

        $this->assertTrue($service->hasValidActiveSession(
            $response['token'],
            'client-1',
            $seriesId
        ));
        $this->assertFalse($service->hasValidActiveSession(
            $response['token'],
            'another-client',
            $seriesId
        ));
    }

    private function createSeries(string $name = 'Lounge test series'): int
    {
        return DB::table('series')->insertGetId([
            'name' => $name,
            'type' => 'tournament',
            'description' => 'Test series',
            'price' => 1000,
            'is_paused' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
