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
        // Numeric names match the existing production convention (9 = U9s).
        $ageGroups = [
            '6' => ['min_age' => 5, 'max_age' => 6],
            '7' => ['min_age' => 6, 'max_age' => 7],
            '8' => ['min_age' => 7, 'max_age' => 8],
            '9' => ['min_age' => 8, 'max_age' => 9],
            '10' => ['min_age' => 9, 'max_age' => 10],
            '11' => ['min_age' => 10, 'max_age' => 11],
            '12' => ['min_age' => 11, 'max_age' => 12],
            '13' => ['min_age' => 12, 'max_age' => 13],
            '14' => ['min_age' => 13, 'max_age' => 14],
            '15' => ['min_age' => 14, 'max_age' => 15],
            '16' => ['min_age' => 15, 'max_age' => 16],
            '18' => ['min_age' => 17, 'max_age' => 18],
            'Open' => ['min_age' => 18, 'max_age' => 40],
        ];

        foreach ($ageGroups as $name => $ages) {
            $ageGroup = AgeGroup::withTrashed()->firstOrNew(['name' => (string) $name]);

            $ageGroup->forceFill([
                'name' => (string) $name,
                'min_age' => $ages['min_age'],
                'max_age' => $ages['max_age'],
            ]);

            if ($ageGroup->trashed()) {
                $ageGroup->restore();
            }

            $ageGroup->save();
        }
    }
}
