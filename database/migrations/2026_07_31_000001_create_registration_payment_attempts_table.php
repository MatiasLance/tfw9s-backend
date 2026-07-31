<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('registration_key', 64)->unique();
            $table->foreignId('series_id')->constrained('series');
            $table->string('gateway', 32);
            $table->string('transaction_id')->nullable()->unique();
            $table->string('status', 32)->default('created');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_payment_attempts');
    }
};
