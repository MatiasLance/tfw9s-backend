<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Item;
use App\Models\Media;
use App\Models\Tag;
use App\Repository\ItemRepositoryInterface;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed root Categories
        Category::factory()
            ->count(5)
            ->create();

        // Seed 2nd level
        Category::factory()
            ->count(20)
            ->create();

        // Seed 2nd level
        Category::factory()
            ->count(50)
            ->create();

        Item::factory()
            ->count(80)
            ->has(
                Tag::factory()->count(2)
            )
            ->has(
                Category::factory()
            )
            ->has(
                Media::factory()
                    ->state(function (array $attributes) {
                        return [
                            'path' => 'media/default/' . ItemRepositoryInterface::PLACEHOLDER_IMAGE
                        ];
                    })
            )
            ->create();
    }
}
