<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\Media;
use App\Models\Tag;
use App\Models\Region;
use App\Models\Event;
use App\Models\News;
use App\Models\Team;
use App\Models\Field;
use App\Models\AgeGroup;
use App\Models\PartnerSponsor;
use App\Models\Guideline;
use App\Models\Variant;
use App\Repository\ItemRepositoryInterface;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        User::factory(11)->create()->each(function ($user) {
            $role = Role::find(3);

            if ($role) {
                $user->assignRole($role);
            }
        });

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

        Item::factory()
            ->count(8)
            ->has(
                Tag::factory()->count(2)
            )
            ->has(
                Category::factory()
            )
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

        $this->call([
            SeriesSeeder::class,
            ManagersSeeder::class,
            EventsSeeder::class,
            TeamsSeeder::class,
            EventMatchesSeeder::class,
            TeamPositionSeeder::class,
            TeamLimitSeeder::class,
            AgeGroupsSeeder::class,
            RegionsSeeder::class,
            FieldsSeeder::class,
            VariantSeeder::class
        ]);
    }
}
