<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;

class ManagersSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $managerUsers = User::whereHas('roles', function ($query) {
            $query->where('roles.id', 3);
        })->get();

        foreach ($managerUsers as $user) {
                DB::table('managers')->insert([
                    'user_id' => $user->id,
                    'date_of_birth' => $faker->date,
                    'address' => $faker->address,
                    'age' => $faker->numberBetween(18, 40),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

        }
    }
}
