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
         Schema::table('item_variant', function (Blueprint $table) {
            // Rename 'color' to 'value' to make it generic
            $table->renameColumn('color', 'value');
            
            // Add type column to distinguish between color/size
            $table->enum('type', ['color', 'size'])->default('color')->after('color');
            
            // Add SKU for inventory tracking
            $table->string('sku')->nullable()->unique()->after('type');
            
            // Add price override for size-based pricing
            $table->integer('price_override')->nullable()->after('sku');
            
            // Add stock tracking per variant
            $table->integer('stock_quantity')->default(0)->after('price_override');
            
            // Add display order
            $table->integer('display_order')->default(0)->after('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_variant', function (Blueprint $table) {
            $table->renameColumn('value', 'color');
            $table->dropColumn(['type', 'sku', 'price_override', 'stock_quantity', 'display_order']);
        });
    }
};
