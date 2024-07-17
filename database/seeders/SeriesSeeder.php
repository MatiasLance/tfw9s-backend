<?php

namespace Database\Seeders;

use App\Modules\TeamLimit\TeamLimitServiceInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Series;

use DateTime;

class SeriesSeeder extends Seeder
{
    protected $teamLimitService;

    // Use constructor injection to get the service
    public function __construct(TeamLimitServiceInterface $teamLimitService)
    {
        $this->teamLimitService = $teamLimitService;
    }
    public function run()
    {
        $faker = Faker::create();
        $currentYear = date('Y');
        $currentMonth = date('m');
        $startDate = $currentYear . '-'.$currentMonth.'-01';
        $endDate = $currentYear . '-'.$currentMonth.'-25';
        $price = mt_rand(200, 500);

        $types = ['weekly', 'tournament', 'coast'];

        foreach ($types as $Type) {
            $eventType = $faker->randomElement(['Cup', 'League', 'Tournament', 'Championship']);
            $eventName = $faker->unique()->state();
            $eventTitle = $eventName . ' ' . $eventType;
            $startDate = $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d');
            $endDate = (new DateTime($startDate))->modify('+3 days')->format('Y-m-d'); // Modify end date to be 3 days after start date
            $price = mt_rand(200, 500);
            $series = Series::create([
                'name' => $eventTitle,
                'type' => $Type,
                'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                'address' => $faker->address(),
                'start' => $startDate,
                'end' => $endDate,
                'price' => $price,
            ]);

            if ($Type != 'weekly') {
                $this->teamLimitService->createTeamLimit($series->id);
            }
        }
    }
}

