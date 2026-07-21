<?php

namespace Tests\Feature\Series;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeriesRetrieveApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrieve_includes_teams_by_default(): void
    {
        $seriesId = $this->createSeries();

        $this->get("/api/v1/series/{$seriesId}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'series' => ['team'],
                ],
            ]);
    }

    public function test_retrieve_can_omit_teams_for_article_pages(): void
    {
        $seriesId = $this->createSeries();

        $this->get("/api/v1/series/{$seriesId}?includeTeams=false")
            ->assertOk()
            ->assertJsonMissingPath('data.series.team');
    }

    private function createSeries(): int
    {
        return DB::table('series')->insertGetId([
            'name' => 'Article performance test series',
            'type' => 'tournament',
            'description' => 'Test series',
            'price' => 1000,
            'is_paused' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
