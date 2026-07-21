<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('waiting_lounge_locks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('waiting_lounge_locks')->insert([
            'name' => 'global_checkout',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('waiting_lounge', function (Blueprint $table) {
            $table->index(
                ['status', 'expires_at'],
                'waiting_lounge_global_status_expiry_index'
            );
        });
    }

    public function down()
    {
        Schema::table('waiting_lounge', function (Blueprint $table) {
            $table->dropIndex('waiting_lounge_global_status_expiry_index');
        });

        Schema::dropIfExists('waiting_lounge_locks');
    }
};
