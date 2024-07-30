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
        Schema::create('individual_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('refund_id')->nullable();
            $table->string('payment_gateway');
            $table->integer('price');
            $table->integer('refund')->default(0);
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
        Schema::dropIfExists('individual_registrations');
    }
};
