<?php

use App\Models\Tag;
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
        $defaultTags = [
            [ 'name' => 'Popular', ],
            [ 'name' => 'Sale', ],
            [ 'name' => 'New', ],
            [ 'name' => 'Good Condition', ],
            [ 'name' => 'Always in stock', ],
            [ 'name' => 'Some blemishes', ],
        ];

        Tag::factory()->createMany($defaultTags);
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
