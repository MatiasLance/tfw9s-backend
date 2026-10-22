<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Region;
use App\Models\Series;
use App\Models\Team;
use Illuminate\Database\Seeder;
use RuntimeException;

class TeamsSeeder extends Seeder
{
    /**
     * Demo teams grouped by age category.
     *
     * The age group names use the application's existing numeric convention:
     * "9" is U9s, "12" is U12s, and "Open" is the senior/open category.
     *
     * @return array<string, array{series: string, region: string, teams: array<int, string>}>
     */
    public static function definitions(): array
    {
        return [
            '9' => [
                'series' => 'Weekly Series',
                'region' => 'Central Coast',
                'teams' => [
                    'Avoca Sharks U9',
                    'Bateau Bay Bears U9',
                    'Erina Eagles U9',
                    'Gosford Giants U9',
                    'Kincumber Colts U9',
                    'Terrigal Titans U9',
                    'The Entrance Eels U9',
                    'Wyong Warriors U9',
                ],
            ],
            '12' => [
                'series' => 'Central Coast',
                'region' => 'Central Coast',
                'teams' => [
                    'Avoca Sharks U12',
                    'Bateau Bay Bears U12',
                    'Erina Eagles U12',
                    'Gosford Giants U12',
                    'Kincumber Colts U12',
                    'Terrigal Titans U12',
                    'The Entrance Eels U12',
                    'Wyong Warriors U12',
                ],
            ],
            'Open' => [
                'series' => 'Tournament',
                'region' => 'Hunter Valley',
                'teams' => [
                    'Central Coast Cobras',
                    'Newcastle Knights Academy',
                    'Maitland Mustangs',
                    'Cessnock Cougars',
                    'Lake Macquarie Lakers',
                    'Singleton Stallions',
                    'Port Stephens Pythons',
                    'Wallsend Wolves',
                ],
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::definitions() as $ageGroupName => $definition) {
            $ageGroup = AgeGroup::withTrashed()->where('name', $ageGroupName)->first();
            $series = Series::withTrashed()->where('name', $definition['series'])->first();
            $region = Region::withTrashed()->where('name', $definition['region'])->first();

            if (! $ageGroup || ! $series || ! $region) {
                throw new RuntimeException(
                    "TeamsSeeder requires the {$ageGroupName} age group, {$definition['series']} series, and {$definition['region']} region."
                );
            }

            foreach ($definition['teams'] as $index => $teamName) {
                $attributes = Team::factory()
                    ->make([
                        'name' => $teamName,
                        'agegroup_id' => $ageGroup->id,
                        'series_id' => $series->id,
                        'region_id' => $region->id,
                        'player_limit' => 24,
                        'pool' => $index % 2 === 0 ? 'A' : 'B',
                    ])
                    ->getAttributes();

                $team = Team::withTrashed()->firstOrNew(['name' => $teamName]);
                $team->forceFill($attributes);

                if ($team->trashed()) {
                    $team->restore();
                }

                $team->save();
            }
        }
    }
}
