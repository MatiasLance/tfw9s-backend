<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist the monetary breakdown of an order so invoices and reports do
     * not have to recalculate totals from changing product/tax settings.
     *
     * Amounts are stored in cents, matching orders.total.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('sub_total')->nullable()->after('total');
            $table->unsignedInteger('shipping_total')->nullable()->after('sub_total');
            $table->unsignedInteger('tax_total')->nullable()->after('shipping_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['sub_total', 'shipping_total', 'tax_total']);
        });
    }
};
