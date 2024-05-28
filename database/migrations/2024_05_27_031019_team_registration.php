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
        Schema::create('team_registration', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('payment_gateway');
            $table->string('coach_name');
            $table->string('coach_email');
            $table->string('coach_number');
            $table->string('team_name');
            $table->string('manager_name');
            $table->string('manager_email');
            $table->string('manager_number');
            $table->integer('age_group');
            $table->integer('price');
            $table->boolean('is_verified')->default(0);
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
        Schema::dropIfExists('team_registration');
    }
};