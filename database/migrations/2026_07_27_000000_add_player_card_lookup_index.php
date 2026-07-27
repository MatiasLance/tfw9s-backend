<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the reverse name index used by last-name predictive searches.
     */
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->index(
                ['player_lastname', 'player_firstname'],
                'players_lastname_firstname_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropIndex('players_lastname_firstname_index');
        });
    }
};
