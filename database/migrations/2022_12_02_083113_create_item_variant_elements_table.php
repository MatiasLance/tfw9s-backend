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
        Schema::create('item_variant_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id');
            $table->foreignId('element_id');
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('stock')->default(0);
            $table->string('thumbnail_type')->nullable(); // Image, color or null
            $table->string('thumbnail_color_value')->nullable();
            $table->unsignedBigInteger('order')->nullable();
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
        Schema::dropIfExists('item_variant_elements');
    }
};
