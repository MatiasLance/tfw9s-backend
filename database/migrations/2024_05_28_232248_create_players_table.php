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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->nullable();
            $table->string('contact_firstname');
            $table->string('contact_lastname');
            $table->string('phone_number');
            $table->string('email');
            $table->string('player_firstname');
            $table->string('player_lastname');
            $table->string('team_name');
            $table->date('dob');
            $table->string('agegroup');
            $table->string('description');
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
        Schema::dropIfExists('players');
    }
};
