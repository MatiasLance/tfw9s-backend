<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use Illuminate\Database\Seeder;

class AgeGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ages = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18];

        foreach ($ages as $age) {
            $name = (string) $age;
            $ageGroup = AgeGroup::withTrashed()->firstOrNew(['name' => $name]);

            $ageGroup->forceFill([
                'name' => $name,
                'min_age' => $age - 1,
                'max_age' => $age,
            ]);

            if ($ageGroup->trashed()) {
                $ageGroup->restore();
            }

            $ageGroup->save();
        }
    }
}
