<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_form_statuses', function (Blueprint $table) {
            $table->string('timer_mode')->default('date')->after('is_show_count_down_timer');
            $table->string('countdown_unit')->nullable();
            $table->integer('countdown_value')->nullable();
            $table->datetime('opens_at')->nullable()->after('timer_mode');
        });

        DB::table('registration_form_statuses')
            ->whereNotNull('date')
            ->update([
                'opens_at' => DB::raw('date'),
                'timer_mode' => 'date'
            ]);

        Schema::table('registration_form_statuses', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    public function down(): void
    {
        Schema::table('registration_form_statuses', function (Blueprint $table) {
            $table->date('date')->nullable();
        });

        DB::table('registration_form_statuses')->update(['date' => DB::raw('opens_at')]);

        Schema::table('registration_form_statuses', function (Blueprint $table) {
            $table->dropColumn(['opens_at', 'timer_mode', 'countdown_unit', 'countdown_value']);
        });
    }
};