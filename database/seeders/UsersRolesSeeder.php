<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersRolesSeeder extends Seeder
{
    /**
     * Demo account password. All seeded accounts use this password.
     */
    public const PASSWORD = 'password';

    /**
     * Seed the roles and a mix of staff and standard user accounts.
     */
    public function run(): void
    {
        foreach (['superadmin', 'admin', 'manager'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $accounts = [
            [
                'email' => 'superadmin@demo.thefinalwhistle.test',
                'first_name' => 'Demo',
                'last_name' => 'Superadmin',
                'role' => 'superadmin',
            ],
            [
                'email' => 'admin@demo.thefinalwhistle.test',
                'first_name' => 'Demo',
                'last_name' => 'Admin',
                'role' => 'admin',
            ],
            [
                'email' => 'manager1@demo.thefinalwhistle.test',
                'first_name' => 'Avery',
                'last_name' => 'Manager',
                'role' => 'manager',
            ],
            [
                'email' => 'manager2@demo.thefinalwhistle.test',
                'first_name' => 'Jordan',
                'last_name' => 'Manager',
                'role' => 'manager',
            ],
            [
                'email' => 'user1@demo.thefinalwhistle.test',
                'first_name' => 'Casey',
                'last_name' => 'Supporter',
                'role' => null,
            ],
            [
                'email' => 'user2@demo.thefinalwhistle.test',
                'first_name' => 'Riley',
                'last_name' => 'Parent',
                'role' => null,
            ],
            [
                'email' => 'user3@demo.thefinalwhistle.test',
                'first_name' => 'Morgan',
                'last_name' => 'Coach',
                'role' => null,
            ],
            [
                'email' => 'user4@demo.thefinalwhistle.test',
                'first_name' => 'Taylor',
                'last_name' => 'Volunteer',
                'role' => null,
            ],
            [
                'email' => 'user5@demo.thefinalwhistle.test',
                'first_name' => 'Jamie',
                'last_name' => 'Spectator',
                'role' => null,
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::withTrashed()->firstOrNew(['email' => $account['email']]);

            $user->forceFill([
                'first_name' => $account['first_name'],
                'last_name' => $account['last_name'],
                'phone' => '0400 000 000',
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
                'subscription_status' => $account['role'] === 'manager' ? '0' : '1',
            ]);

            if ($user->trashed()) {
                $user->restore();
            }

            $user->save();

            // syncRoles is idempotent and also clears roles from standard users
            // that may have been assigned during a previous seed run.
            $user->syncRoles($account['role'] ? [$account['role']] : []);
        }
    }
}
