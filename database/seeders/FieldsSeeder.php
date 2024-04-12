<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Field;
use App\Models\Region;

class FieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $regionIds = Region::pluck('id')->toArray();

        $fields = [
            'Rugby Park',
            'Scrum Grounds',
            'Tryline Stadium',
            'Lineout Field',
            'Kickoff Grounds',
            'Conversion Arena',
            'Ruck Field',
            'Maul Grounds',
            'Breakdown Stadium',
            'Tackle Zone',
            'Grubber Park',
            'Offload Grounds',
            'Passing Arena',
            'Tighthead Field',
            'Loosehead Grounds'
        ];        

        foreach ($fields as $field) {
            Field::create([
                'name' => $field,
                'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                'region_id' => $faker->randomElement($regionIds),
            ]);
        }
    }
}
