<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the delivery and pickup fees to the existing shipping options
     * record. Amounts are stored in cents to match the rest of the order
     * money columns.
     */
    public function up()
    {
        Schema::table('shipping_options', function (Blueprint $table) {
            $table->unsignedInteger('delivery_charge')
                ->default(1000)
                ->after('pickup_note');

            $table->unsignedInteger('pickup_charge')
                ->default(0)
                ->after('delivery_charge');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down()
    {
        Schema::table('shipping_options', function (Blueprint $table) {
            $table->dropColumn(['delivery_charge', 'pickup_charge']);
        });
    }
};
