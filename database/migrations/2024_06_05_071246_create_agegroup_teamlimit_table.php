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
        Schema::create('agegroup_teamlimit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agegroup_id')->onDelete('cascade');
            $table->foreignId('teamlimit_id')->onDelete('cascade');
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
        Schema::dropIfExists('agegroup_teamlimit');
    }
};
