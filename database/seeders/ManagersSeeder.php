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

        $managerUsers = $managerUsers->reject(function ($user) {
            return $user->email === 'manager@thefinalwhistle.com';
        });

        foreach ($managerUsers as $user) {
                DB::table('managers')->insert([
                    'user_id' => $user->id,
                    'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

        }
    }
}
