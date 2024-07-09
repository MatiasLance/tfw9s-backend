<?php

use App\Models\HomePageInformation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        HomePageInformation::factory()->create([
            'blurb' => '<p>The Final Whistle is a dynamic platform dedicated to sharing uplifting stories from Junior Rugby League. Through engaging content and vibrant storytelling, it celebrates the achievements, resilience, and sportsmanship of young athletes. By amplifying these positive narratives, it aims to inspire and empower the rugby league community, fostering a culture of inclusivity and encouragement. With a focus on teamwork, perseverance, and community spirit, The Final Whistle showcases the transformative power of sport in shaping young lives.\n(Temporary Statement)</p>',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
