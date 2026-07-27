<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManagersSeeder extends Seeder
{
    public function run(): void
    {
        $managerUsers = User::whereHas('roles', function ($query) {
            $query->where('roles.id', 3);
        })->get();

        $managerUsers = $managerUsers->reject(function ($user) {
            return $user->email === 'manager@thefinalwhistle.com';
        });

        foreach ($managerUsers as $user) {
            DB::table('managers')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'description' => fake()->sentence(12),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
