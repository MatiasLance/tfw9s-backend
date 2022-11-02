<?php

namespace App\Repository\Eloquent;

use App\Models\Category;
use App\Modules\Item\Exceptions\CategoryCannotLoopException;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\Eloquent\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }

    public function list(): Collection
    {
        return $this->model
                        ->where('parent_id', null)
                        ->get();
    }

    public function countTotal(): int
    {
        return $this->model->count();
    }

    public function retrieve(int $id): Category
    {
        return $this->find($id);
    }

    public function create(string $name, ?int $parentId = null): Category
    {
        if (!is_null($parentId)) {
            $parentId = $this->find($parentId)->id;
        }
        
        $category = new Category();
        $category->parent_id = $parentId;
        $category->name = $name;

        DB::transaction(function() use($category){
            $category->save();
        });

        return $category;
    }

    public function update(int $id, string $name, ?int $parentId = null): bool
    {
        $category = $this->find($id);
        $category->parent_id = $parentId;
        $category->name = $name;

        return DB::transaction(function() use($category) {
            return $category->save();
        });
    }

    public function move(array $categories, ?int $target): bool
    {
        DB::transaction(function() use($categories, $target){
            $targetExists = Category::where('id', $target)->exists();

            if (!is_null($target)) {
                $targetCategory = $this->model->find($target);
                $targetLineage = $this->getLineage($targetCategory);
            } else {
                $targetLineage = [];
            }
            
            if (is_null($target) || $targetExists) {
                $targetLineageIntersect = array_intersect($targetLineage, $categories);
                if (count($targetLineageIntersect) > 0) {
                    throw new CategoryCannotLoopException();
                } else {
                    foreach ($categories as $categoryId) {
                        $category = $this->find($categoryId);

                        $categoryLineage = $this->getLineage($category);
                        $isTargetInCategoryLineage = in_array($target, $categoryLineage);
                        if (!$isTargetInCategoryLineage) {
                            $category->parent_id = $target;
                            $category->save();
                        } else {
                            throw new CategoryCannotLoopException();
                        }
                    }
                }
            }
        });

        return true;
    }

    public function delete(int $id): bool
    {
        $category = $this->find($id);

        return DB::transaction(function() use($category) {
            $subCategories = $category->children;
            foreach ($subCategories as $subCategory) {
                $this->delete($subCategory->id);
            }

            foreach ($category->items as $item) {
                $category->items()->detach($item);
            }

            return $category->delete();
        });
    }

    /**
     * Get the IDs of the categories under the given category. Also includes
     * the ID of the given category. Returns an array of IDs
     * 
     * @param Category $category
     * 
     * @return array|int
     */
    protected function getLineage(Category $category): array
    {
        $lineage = [];
        $children = $category->children;
        if (count($children) > 0) {
            foreach ($children as $childCategory) {
                 $line = $this->getLineage($childCategory);
                 $lineage = array_merge($lineage, $line);
            }
            array_push($lineage, $category->id);
        } else {
            return [$category->id];
        }

        return $lineage;
    }
}