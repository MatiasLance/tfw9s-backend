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
        Schema::create('new_shippings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->float('shipping_value');
            $table->float('insurance_value');
            $table->float('registered_value');
            $table->float('express_value');
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
        Schema::dropIfExists('new_shippings');
    }
};
