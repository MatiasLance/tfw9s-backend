<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Guideline;
use App\Models\News;
use App\Models\PartnerSponsor;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: reference data is created first, then the demo teams and
     * players, followed by events, fixtures, and finally the ladder built from
     * those fixtures.
     */
    public function run(): void
    {
        $this->call([
            // Accounts and permissions.
            UsersRolesSeeder::class,

            // Reference data used by teams and events.
            RegionsSeeder::class,
            FieldsSeeder::class,
            AgeGroupsSeeder::class,
            SeriesSeeder::class,
            TeamLimitSeeder::class,

            // Teams and players for U9s, U12s, and Open.
            TeamsSeeder::class,
            PlayersSeeder::class,
            ManagersSeeder::class,

            // Past/current/upcoming events, fixtures (including byes), ladder.
            EventsSeeder::class,
            EventMatchesSeeder::class,
            TeamPositionSeeder::class,

            // Storefront content.
            VariantSeeder::class,
            ItemSeeder::class,
        ]);

        // Seed root Categories
        Category::factory()
            ->count(5)
            ->create();

        // Seed 2nd level
        Category::factory()
            ->count(10)
            ->create();

        // Seed 2nd level
        Category::factory()
            ->count(20)
            ->create();

        News::factory()
            ->count(15)
            ->create();

        Guideline::factory()
            ->count(15)
            ->create();

        PartnerSponsor::factory()
            ->count(5)
            ->create();
    }
}
