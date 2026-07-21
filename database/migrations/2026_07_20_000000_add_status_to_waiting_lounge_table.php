<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('waiting_lounge', function (Blueprint $table) {
            $table->string('status', 20)->default('waiting')->after('client_id');
            $table->index(
                ['series_id', 'status', 'expires_at'],
                'waiting_lounge_series_status_expiry_index'
            );
        });
    }

    public function down()
    {
        Schema::table('waiting_lounge', function (Blueprint $table) {
            $table->dropIndex('waiting_lounge_series_status_expiry_index');
            $table->dropColumn('status');
        });
    }
};
