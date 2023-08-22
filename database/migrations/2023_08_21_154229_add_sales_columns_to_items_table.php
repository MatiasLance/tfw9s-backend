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
        Schema::table('items', function (Blueprint $table) {
            $table->integer('saleprice')->nullable()->after('price');
            $table->boolean('show_rrp')->default(false);
            $table->boolean('is_on_sale')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            // Remove the new columns if needed
            $table->dropColumn('saleprice');
            $table->dropColumn('show_rrp');
            $table->dropColumn('is_on_sale');
        });
    }
};
