<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $hidden = [
        'parent_id',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'children'
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_categories');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Retrieve list of descendants
     * 
     * @return array
     */
    public function descendants()
    {
        return $this->getDescendants($this);
    }

    /**
     * Trace the descendants of the given category including itself
     * 
     * @param Category $category
     * 
     * @return array
     */
    protected function getDescendants(Category $category)
    {
        $lineage = [];
        foreach ($category->children as $child) {
            $descendantIds = $this->getDescendants($child);
            $lineage = array_merge($lineage, $descendantIds);
        }

        array_push($lineage, $category->id);
        return $lineage;
    }

    /**
     * Retrieve the list of ancestors this category has.
     * 
     * @return array
     */
    public function ancestors()
    {
        return $this->getAncestor($this);
    }

    /**
     * Trace the ancestors of the given category including itself
     * 
     * @param Category $category
     * 
     * @return array
     */
    protected function getAncestor(Category $category): array
    {
        $parent = $category->parent;
        $lineage = [];

        if (!is_null($parent)) {
            $ancestorIds = $this->getAncestor($parent);
            array_push($lineage, $category->id);
            return array_merge($lineage, $ancestorIds);
        } else {
            return [$category->id];
        }
    }
}
