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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('payment_gateway'); // Use PaymentGateway enum
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone_number');
            $table->string('email');
            $table->string('shipping_type');
            $table->string('address')->nullable();
            $table->string('post_code')->nullable();
            $table->mediumText('remarks');
            $table->integer('total');
            $table->boolean('is_verified')->default(false);
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
        Schema::dropIfExists('orders');
    }
};
