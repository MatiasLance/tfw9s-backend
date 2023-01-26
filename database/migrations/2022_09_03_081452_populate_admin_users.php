<?php

use App\Models\User;
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
        $users = [
            [               
                'email' => env('ADMIN_EMAIL_ADDRESS', 'admin@thedrumhq.com.au'),
                'password' => bcrypt('superuser1'),
                'first_name' => 'Admin',
                'last_name' => '',
                'phone' => ''
            ],
        ];

        User::factory()->createMany($users);
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
