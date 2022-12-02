<?php

namespace Database\Seeders;

use App\Models\Element;
use App\Models\Variant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Variant::factory()
                    ->count(3)
                    ->has(
                        Element::factory()
                                ->count(3)
                    )
                    ->create();
    }
}
