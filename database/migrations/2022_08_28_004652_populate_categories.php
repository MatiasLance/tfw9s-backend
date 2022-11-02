<?php

use App\Models\Category;
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
        $categories = [
            [
                'name' => 'Scenes',
                'children' => [],
            ],
            [
                'name' => 'Birds and Animals',
                'children' => [],
            ],
            [
                'name' => 'Party',
                'children' => [],
            ],
            [
                'name' => 'Other Places',
                'children' => [],
            ],
            [
                'name' => 'Art',
                'children' => []
            ],
            [
                'name' => 'Tshirts',
                'children' => []
            ]
        ];

        foreach ($categories as $category) {
            $this->generateCategory($category);
        }
    }

    protected function generateCategory($category, $parentId = null)
    {
        $x = new Category();
        $x->name = $category['name'];
        $x->parent_id = $parentId;
        $x->save();

        foreach ($category['children'] as $child) {
            $this->generateCategory($child, $x->id);
        }
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
