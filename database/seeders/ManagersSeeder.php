<?php

namespace Database\Seeders;

use App\Models\Manager;
use App\Models\User;
use Illuminate\Database\Seeder;

class ManagersSeeder extends Seeder
{
    /**
     * Create a manager profile for every user with the manager role.
     */
    public function run(): void
    {
        $managerUsers = User::query()
            ->role('manager')
            ->orderBy('id')
            ->get();

        foreach ($managerUsers as $user) {
            $manager = Manager::withTrashed()
                ->where('user_id', $user->id)
                ->first() ?? new Manager();

            $manager->forceFill([
                'user_id' => $user->id,
                'description' => "{$user->first_name} {$user->last_name} manages the weekly competition fixtures.",
            ]);

            if ($manager->trashed()) {
                $manager->restore();
            }

            $manager->save();
        }
    }
}
