<?php

use App\Models\HomePageInformation;
use App\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the HomePageInformation record
        $homePageInformation = HomePageInformation::factory()->create([
            'blurb' => '<p>The Final Whistle is a dynamic platform dedicated to sharing uplifting stories from Junior Rugby League. Through engaging content and vibrant storytelling, it celebrates the achievements, resilience, and sportsmanship of young athletes. By amplifying these positive narratives, it aims to inspire and empower the rugby league community, fostering a culture of inclusivity and encouragement. With a focus on teamwork, perseverance, and community spirit, The Final Whistle showcases the transformative power of sport in shaping young lives.\n(Temporary Statement)</p>',
        ]);

        // Create the associated Media record
        Media::create([
            'imageable_type' => HomePageInformation::class,
            'imageable_id' => $homePageInformation->id,
            'hash' => Str::random(64),
            'path' => 'media/default/kidsplaying.jpg',
            'format' => 'jpg',
            'mime_type' => 'image/jpeg',
            'size' => random_int(300, 50000),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optionally, delete the HomePageInformation and associated Media record
        $homePageInformation = HomePageInformation::where('blurb', 'LIKE', '%The Final Whistle%')->first();
        if ($homePageInformation) {
            Media::where('imageable_type', HomePageInformation::class)
                 ->where('imageable_id', $homePageInformation->id)
                 ->delete();
            $homePageInformation->delete();
        }
    }
};
