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
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('agegroup_id');
            $table->dropColumn('coach_email');
            $table->dropColumn('max_registration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('series', function (Blueprint $table) {
            $table->string('agegroup_id')->nullable()->after('id');
            $table->string('coach_email')->nullable()->after('name');
            $table->unsignedBigInteger('max_registration')->default(0)->after('price');
        });
    }
};
