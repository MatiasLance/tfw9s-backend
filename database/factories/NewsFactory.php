<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        return [
            'headline' => 'in quam feugiat tristique nec id urna.',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Curabitur commodo est in quam feugiat tristique nec id urna. Duis eu neque tempor, aliquam nisl eget, dignissim dolor.
            Aenean finibus imperdiet porttitor.',
        ];
    }
}
