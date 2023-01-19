<?php

use App\Models\ToggleMasterShippingSetting;
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
        ToggleMasterShippingSetting::factory()->create([
            'togglemastersetting1' => boolval(true),
            'togglemastersetting2' => boolval(true),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
