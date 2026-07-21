<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add non-unique indexes used by the public Draw listing and standings rebuild.
     *
     * No foreign keys or uniqueness constraints are added here because existing
     * production data must be audited before those constraints can be introduced.
     */
    public function up(): void
    {
        Schema::table('event_matches', function (Blueprint $table) {
            $table->index(
                ['event_id', 'deleted_at', 'submitted'],
                'draw_event_matches_event_deleted_submitted_idx'
            );
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index(
                ['deleted_at', 'event_date', 'time', 'id'],
                'draw_events_deleted_date_time_id_idx'
            );
        });

        Schema::table('media', function (Blueprint $table) {
            $table->index(
                ['imageable_type', 'imageable_id'],
                'draw_media_imageable_type_id_idx'
            );
        });

        Schema::table('team_positions', function (Blueprint $table) {
            $table->index(
                ['event_id', 'team_id'],
                'draw_team_positions_event_team_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('team_positions', function (Blueprint $table) {
            $table->dropIndex('draw_team_positions_event_team_idx');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('draw_media_imageable_type_id_idx');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('draw_events_deleted_date_time_id_idx');
        });

        Schema::table('event_matches', function (Blueprint $table) {
            $table->dropIndex('draw_event_matches_event_deleted_submitted_idx');
        });
    }
};
