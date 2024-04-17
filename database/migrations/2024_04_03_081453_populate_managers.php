<?php

use App\Models\User;
use App\Models\Manager;
use Spatie\Permission\Models\Role;
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
        $managerUser = User::where('email', 'manager@thefinalwhistle.com')->first();

        if ($managerUser) {
            Manager::insert([
                'user_id' => $managerUser->id,
                'description' => 'Test Manager Description',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
