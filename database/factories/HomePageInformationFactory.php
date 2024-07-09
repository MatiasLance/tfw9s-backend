<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HomePageInformation>
 */
class HomePageInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'blurb' => '<p>The Final Whistle is a dynamic platform dedicated to sharing uplifting stories from Junior Rugby League. Through engaging content and vibrant storytelling, it celebrates the achievements, resilience, and sportsmanship of young athletes. By amplifying these positive narratives, it aims to inspire and empower the rugby league community, fostering a culture of inclusivity and encouragement. With a focus on teamwork, perseverance, and community spirit, The Final Whistle showcases the transformative power of sport in shaping young lives.\n(Temporary Statement)</p>'
        ];
    }
}
