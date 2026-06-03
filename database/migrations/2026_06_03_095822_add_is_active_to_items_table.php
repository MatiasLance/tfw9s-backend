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
            $columnPosition = Schema::hasColumn('items', 'is_featured')
                ? 'is_featured'
                : 'colors';

            if ($columnPosition) {
                $table->boolean('is_active')
                      ->after($columnPosition)
                      ->default(true)
                      ->comment('Controls item visibility to end users');
            } else {
                $table->boolean('is_active')
                      ->default(true)
                      ->comment('Controls item visibility to end users');
            }
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
            $table->dropColumn('is_active');
        });
    }
};
