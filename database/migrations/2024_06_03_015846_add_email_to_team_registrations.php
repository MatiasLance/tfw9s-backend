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
        Schema::table('team_registrations', function (Blueprint $table) {
            $table->string('coach_email')->after('payment_gateway');
            $table->string('manager_email')->after('payment_gateway');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_registrations', function (Blueprint $table) {
            $table->dropColumn('coach_email');
            $table->dropColumn('manager_email');
        });
    }
};
