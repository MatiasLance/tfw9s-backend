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
        Schema::create('toggle_master_shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('togglemastersetting1')->default(1);
            $table->boolean('togglemastersetting2')->default(1);
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
        Schema::dropIfExists('toggle_master_shipping_settings');
    }
};
