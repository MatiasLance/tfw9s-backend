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
            $table->text('description');
            $table->foreignId('registration_id');
            $table->foreignId('field_id')->nullable();
            $table->foreignId('agegroup_id')->nullable();
            $table->foreignId('event_id')->nullable();
            $table->string('coach_name')->nullable();
            $table->string('coach_mobile')->nullable();
            $table->string('coach_email')->unique()->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_mobile')->nullable();
            $table->string('manager_email')->unique()->nullable();
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
