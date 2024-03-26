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
        Schema::create('event_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id');
            $table->dateTime('event_date');
            $table->time('match_time');
            $table->foreignId('team1');
            $table->foreignId('team2');
            $table->integer('team1_score')->default(0);
            $table->integer('team2_score')->default(0);
            $table->string('winner')->default('none');
            $table->string('losser')->default('none');
            $table->boolean('isDraw')->default(false);
            $table->foreignId('event_match_video_id')->nullable()->change();
            $table->softDeletes();
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
        Schema::dropIfExists('event_matches');
    }
};
