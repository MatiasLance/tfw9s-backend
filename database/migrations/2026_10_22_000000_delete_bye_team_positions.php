<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove legacy bye rows from the ladder.
     *
     * A fixture bye is stored on `event_matches` as `team2 = 0`. The fixture
     * creation/update logic used to create a matching `team_positions` row for
     * it as well, which made the public ladder render an "Unknown Team" entry
     * (no team exists with id 0). Fixture creation and updates now skip the
     * bye sentinel; this migration clears out the rows that were already
     * written by the previous behaviour.
     *
     * This is a data cleanup only: no columns or indexes are changed.
     */
    public function up(): void
    {
        DB::table('team_positions')
            ->where('team_id', '<=', 0)
            ->delete();
    }

    public function down(): void
    {
        // Invalid bye positions must not be restored.
    }
};
