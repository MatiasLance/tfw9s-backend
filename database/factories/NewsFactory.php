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
            'headline' => $this->faker->realText($maxNbChars = 50, $indexSize = 2),
            'content' => '<div class="indent-8 text-justify">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Turpis egestas sed tempus urna. Morbi leo urna molestie at. Pretium nibh ipsum consequat nisl vel pretium lectus. Pharetra massa massa ultricies mi quis. Sed viverra ipsum nunc aliquet bibendum. Tortor condimentum lacinia quis vel. Nisi quis eleifend quam adipiscing vitae. Erat imperdiet sed euismod nisi porta lorem mollis aliquam. Sollicitudin tempor id eu nisl nunc mi ipsum. Tristique senectus et netus et malesuada fames. Fames ac turpis egestas integer eget aliquet nibh praesent tristique. Venenatis cras sed felis eget. Sagittis aliquam malesuada bibendum arcu vitae. Nascetur ridiculus mus mauris vitae ultricies leo integer malesuada. Morbi blandit cursus risus at. Aliquam nulla facilisi cras fermentum odio eu feugiat pretium nibh. Nibh sit amet commodo nulla facilisi nullam vehicula ipsum a. Venenatis tellus in metus vulputate eu scelerisque felis imperdiet proin. Bibendum neque egestas congue quisque egestas diam in.
            <br/><br/>
            Egestas maecenas pharetra convallis posuere. Id velit ut tortor pretium viverra. Sed euismod nisi porta lorem mollis aliquam ut porttitor. Justo laoreet sit amet cursus sit amet. Tristique et egestas quis ipsum suspendisse ultrices gravida dictum. Ac feugiat sed lectus vestibulum mattis. In mollis nunc sed id semper risus in hendrerit gravida. Ipsum a arcu cursus vitae. Eget sit amet tellus cras adipiscing enim eu turpis egestas. Sapien pellentesque habitant morbi tristique senectus. Justo donec enim diam vulputate ut pharetra sit amet aliquam. Molestie nunc non blandit massa enim nec dui nunc mattis. Consectetur adipiscing elit ut aliquam purus sit. At varius vel pharetra vel turpis nunc eget. A cras semper auctor neque vitae tempus quam pellentesque. Eget arcu dictum varius duis at consectetur. Amet nisl suscipit adipiscing bibendum est ultricies integer quis.
            <br/><br/>
            Turpis nunc eget lorem dolor. Cras fermentum odio eu feugiat pretium nibh ipsum consequat nisl. Neque ornare aenean euismod elementum nisi quis eleifend quam adipiscing. In dictum non consectetur a. Adipiscing commodo elit at imperdiet dui accumsan. Pulvinar etiam non quam lacus suspendisse faucibus interdum. Sit amet mattis vulputate enim nulla aliquet porttitor lacus. Ut faucibus pulvinar elementum integer. Velit egestas dui id ornare arcu odio. Tortor consequat id porta nibh venenatis cras. Orci porta non pulvinar neque laoreet. Neque viverra justo nec ultrices dui sapien eget mi. Pulvinar neque laoreet suspendisse interdum. Nec dui nunc mattis enim ut. Suspendisse sed nisi lacus sed viverra tellus. Cras fermentum odio eu feugiat pretium nibh ipsum consequat. Diam ut venenatis tellus in metus. Ultrices mi tempus imperdiet nulla malesuada pellentesque elit. Sed faucibus turpis in eu mi. Sodales ut eu sem integer vitae justo eget.
            </div>',
        ];
    }
}
