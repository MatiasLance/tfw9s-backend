<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id');
            $table->integer('position');
            $table->foreignId('team_id');
            $table->integer('win')->default(0);
            $table->integer('loss')->default(0);
            $table->integer('draw')->default(0);
            $table->integer('for')->default(0);
            $table->integer('against')->default(0);
            $table->integer('difference')->default(0);
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_positions');
    }
};
