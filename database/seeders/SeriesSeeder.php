<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Modules\TeamLimit\TeamLimitServiceInterface;
use Illuminate\Database\Seeder;

class SeriesSeeder extends Seeder
{
    protected TeamLimitServiceInterface $teamLimitService;

    public function __construct(TeamLimitServiceInterface $teamLimitService)
    {
        $this->teamLimitService = $teamLimitService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = now()->startOfWeek();

        $seriesGroups = [
            'weekly' => [
                'names' => [
                    'Weekly Series',
                    'Monday Night Weekly Series',
                    'Tuesday Twilight Weekly Series',
                    'Wednesday Warriors Weekly Series',
                    'Thursday Thunder Weekly Series',
                    'Friday Lights Weekly Series',
                    'Saturday Social Weekly Series',
                    'Sunday Funday Weekly Series',
                    'Northern Beaches Weekly Series',
                    'Eastern Suburbs Weekly Series',
                    'Western Sydney Weekly Series',
                    'Inner West Weekly Series',
                    'Hills District Weekly Series',
                    'South Sydney Weekly Series',
                    'Harbour City Weekly Series',
                    'Metro Mixed Weekly Series',
                    'Premier Open Weekly Series',
                    'Community Champions Weekly Series',
                    'Rising Stars Weekly Series',
                    'All Seasons Weekly Series',
                ],
                'address' => 'Sydney, NSW',
                'price_start' => 200,
                'start_offset' => 0,
                'interval' => 1,
                'duration' => 12,
            ],
            'tournament' => [
                'names' => [
                    'Tournament',
                    'Sydney Harbour Cup Tournament',
                    'Summer Kickoff Tournament',
                    'Autumn Champions Tournament',
                    'Winter Classic Tournament',
                    'Spring Trophy Tournament',
                    'City Lights Tournament',
                    'Golden Boot Tournament',
                    'Champions Shield Tournament',
                    'Metro Masters Tournament',
                    'Community Cup Tournament',
                    'Rising Stars Tournament',
                    'Weekend Warriors Tournament',
                    'Premier Challenge Tournament',
                    'Coastal Clash Tournament',
                    'Statewide Showdown Tournament',
                    'Unity Cup Tournament',
                    'Legends Trophy Tournament',
                    'Next Generation Tournament',
                    'Final Whistle Tournament',
                ],
                'address' => 'Sydney, NSW',
                'price_start' => 310,
                'start_offset' => 4,
                'interval' => 2,
                'duration' => 3,
            ],
            'coast' => [
                'names' => [
                    'Central Coast',
                    'Central Coast Summer Cup',
                    'Central Coast Autumn Classic',
                    'Central Coast Winter League',
                    'Central Coast Spring Championship',
                    'Central Coast Mariners Challenge',
                    'Central Coast Community Cup',
                    'Central Coast Golden Boot Series',
                    'Central Coast Harbour Trophy',
                    'Central Coast Beachside Cup',
                    'Central Coast Rising Stars',
                    'Central Coast Weekend League',
                    'Central Coast Premier Shield',
                    'Central Coast Unity Cup',
                    'Central Coast Coastal Clash',
                    'Central Coast Champions Trophy',
                    'Central Coast Youth Challenge',
                    'Central Coast Open Championship',
                    'Central Coast Legends Cup',
                    'Central Coast Final Whistle Series',
                ],
                'address' => 'Central Coast, NSW',
                'price_start' => 420,
                'start_offset' => 8,
                'interval' => 2,
                'duration' => 3,
            ],
        ];

        $seriesList = [];

        foreach ($seriesGroups as $type => $group) {
            foreach ($group['names'] as $index => $name) {
                $seriesStart = $startDate->copy()->addWeeks(
                    $group['start_offset'] + ($index * $group['interval'])
                );
                $seriesEnd = $seriesStart->copy();

                if ($type === 'weekly') {
                    $seriesEnd->addWeeks($group['duration']);
                } else {
                    $seriesEnd->addDays($group['duration']);
                }

                $seriesList[] = [
                    'name' => $name,
                    'type' => $type,
                    'description' => "{$name} football competition.",
                    'address' => $group['address'],
                    'start' => $seriesStart->toDateString(),
                    'end' => $seriesEnd->toDateString(),
                    'price' => $group['price_start'] + ($index * 5),
                ];
            }
        }

        foreach ($seriesList as $seriesData) {
            $series = Series::query()
                ->where('name', $seriesData['name'])
                ->first() ?? new Series();

            foreach ($seriesData as $attribute => $value) {
                $series->{$attribute} = $value;
            }

            $series->save();

            if (
                $series->type !== 'weekly'
                && ! $series->teamlimit()->exists()
            ) {
                $this->teamLimitService->createTeamLimit($series->id);
            }
        }
    }
}
