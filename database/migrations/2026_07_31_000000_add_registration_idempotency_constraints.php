<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('individual_registrations', function (Blueprint $table) {
            $table->string('registration_key', 64)->nullable()->after('transaction_id');
            $table->unique('transaction_id', 'individual_registrations_transaction_unique');
            $table->unique('registration_key', 'individual_registrations_registration_unique');
        });
    }

    public function down(): void
    {
        Schema::table('individual_registrations', function (Blueprint $table) {
            $table->dropUnique('individual_registrations_transaction_unique');
            $table->dropUnique('individual_registrations_registration_unique');
            $table->dropColumn('registration_key');
        });
    }
};
