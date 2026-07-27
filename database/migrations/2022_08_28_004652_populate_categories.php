<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            foreach ($this->categoryTree() as $category) {
                $this->createCategoryTree($category);
            }
        });
    }

    /**
     * Create a category and recursively attach its descendants.
     *
     * @param array<string, mixed> $category
     * @param int|null             $parentId
     *
     * @return void
     */
    protected function createCategoryTree(array $category, $parentId = null)
    {
        $model = new Category();
        $model->name = $category['name'];
        $model->parent_id = $parentId;
        $model->save();

        foreach ($category['children'] ?? [] as $child) {
            $this->createCategoryTree($child, $model->id);
        }
    }

    /**
     * Return the default category hierarchy.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function categoryTree()
    {
        return [
            [
                'name' => 'Singlets',
                'children' => $this->sizeGroups(),
            ],
            [
                'name' => 'Hoodies',
                'children' => $this->sizeGroups(),
            ],
            [
                'name' => 'Shorts',
                'children' => $this->sizeGroups(),
            ],
            [
                'name' => 'Beanies',
                'children' => [],
            ],
            [
                'name' => 'Umbrella',
                'children' => [],
            ],
            [
                'name' => 'Heritage',
                'children' => [],
            ],
        ];
    }

    /**
     * Return the shared adult and youth sizing hierarchy.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function sizeGroups()
    {
        return [
            [
                'name' => 'Adult',
                'children' => $this->leafCategories([
                    'Small',
                    'Medium',
                    'Large',
                    'Extra Large',
                    '2 XL',
                ]),
            ],
            [
                'name' => 'Youth',
                'children' => $this->leafCategories([
                    '6',
                    '8',
                    '10',
                    '12',
                    '14',
                ]),
            ],
        ];
    }

    /**
     * Convert category names into consistently shaped leaf nodes.
     *
     * @param array<int, string> $names
     *
     * @return array<int, array<string, mixed>>
     */
    protected function leafCategories(array $names)
    {
        return array_map(function ($name) {
            return [
                'name' => $name,
                'children' => [],
            ];
        }, $names);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $rootNames = array_column($this->categoryTree(), 'name');

        DB::transaction(function () use ($rootNames) {
            $roots = Category::withTrashed()
                ->whereNull('parent_id')
                ->whereIn('name', $rootNames)
                ->get();

            foreach ($roots as $root) {
                $this->deleteCategoryTree($root);
            }
        });
    }

    /**
     * Permanently remove a category and all of its descendants.
     *
     * @param \App\Models\Category $category
     *
     * @return void
     */
    protected function deleteCategoryTree(Category $category)
    {
        $children = Category::withTrashed()
            ->where('parent_id', $category->id)
            ->get();

        foreach ($children as $child) {
            $this->deleteCategoryTree($child);
        }

        $category->forceDelete();
    }
};
