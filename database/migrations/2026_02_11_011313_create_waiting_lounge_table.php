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
        Schema::create('waiting_lounge', function (Blueprint $table) {
          $table->id();
          $table->integer('series_id');
          $table->string('client_id');
          $table->timestamp('expires_at');
          $table->timestamps();

          $table->index(['series_id', 'expires_at']);
          $table->index(['series_id', 'id']);
          $table->unique('client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('waiting_lounge');
    }
};
