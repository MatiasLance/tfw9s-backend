<?php

use App\Models\User;
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
        $superadmin = User::factory()->create([
            'email' => 'superadmin@thefinalwhistle.com',
            'password' => bcrypt('superuser1'),
            'first_name' => 'Super',
            'last_name' => 'Administrator',
            'phone' => '',
            'subscription_status' => '1',
        ]);

        $superadmin->assignRole('superadmin');

        $admin = User::factory()->create([
            'email' => 'admin@thefinalwhistle.com',
            'password' => bcrypt('superuser1'),
            'first_name' => 'Main',
            'last_name' => 'Administaror',
            'phone' => '',
            'subscription_status' => '1',
        ]);

        $admin->assignRole('admin');

        $manager = User::factory()->create([
            'email' =>  'manager@thefinalwhistle.com',
            'password' => bcrypt('superuser1'),
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'phone' => '',
            'subscription_status' => '0' // not subscribe by default
        ]);

        $manager->assignRole('manager');
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
