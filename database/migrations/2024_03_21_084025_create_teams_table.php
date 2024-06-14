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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('registration_id')->nullable();
            $table->foreignId('agegroup_id');
            $table->foreignId('series_id');
            $table->string('coach_name');
            $table->string('coach_mobile')->unique();
            $table->string('coach_email')->unique();
            $table->string('manager_name');
            $table->string('manager_mobile')->unique();
            $table->string('manager_email')->unique();
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
        Schema::dropIfExists('teams');
    }
};
