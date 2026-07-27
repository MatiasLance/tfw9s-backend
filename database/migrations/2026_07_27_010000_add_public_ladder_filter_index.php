<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(
                ['agegroup_id', 'series_id', 'event_date', 'round', 'deleted_at'],
                'events_public_ladder_filters_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_public_ladder_filters_idx');
        });
    }
};
