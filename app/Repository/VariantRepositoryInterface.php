<?php

namespace App\Repository;

use App\Models\Element;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface VariantRepositoryInterface
{

    /**
     * Default filters for the list function
     * 
     * @var array DEFAULT_LIST_FILTERS
     */
    public const DEFAULT_LIST_FILTERS = [
        /**
         * Search query
         */
        'q' => null,
    ];

    /**
     * List all variants and its elements
     * 
     * @param array $userFilters
     * 
     * @return Collection
     */
    public function list(array $userFilters = []): Collection;
    
    /**
     * Retrieve a single Variant and its Elements
     * 
     * @param int $id
     * 
     * @return Variant
     */
    public function retrieveVariant(int $id): Variant;
    
    /**
     * Retrieve a single Element
     * 
     * @param int $id
     * 
     * @return Element
     */
    public function retrieveElement(int $id): Element;
    
    /**
     * Create a new variant
     * 
     * @param string $name
     * 
     * @return Variant
     */
    public function createVariant(string $name): Variant;
    
    /**
     * Create a new Element under an existing Variant
     * 
     * @param int $variantId
     * @param string $name
     * @param string|null $thumbnailType
     * @param string|UploadedFile|null $thumbnail
     * @param int|null $order
     * 
     * @return Variant
     */
    public function createElements(int $variantId, string $name, ?string $thumbnailType, $thumbnail, ?int $order = null): Variant;
    
    /**
     * Update an existing Variant
     * 
     * @param int $id
     * @param string $name
     * 
     * @return bool
     */
    public function updateVariant(int $id, string $name): bool;
    
    /**
     * Update an existing element
     * 
     * @param int $elementId
     * @param string $name
     * @param string|null $thumbnailType
     * @param string|UploadedFile $thumbnail
     * @param int|null $order
     * 
     * @return bool
     */
    public function updateElements(int $elementId, string $name, ?string $thumbnailType, $thumbnail, ?int $order = null): bool;
    
    /**
     * Delete an existing variant and its elements
     * 
     * This will also affect all the items that are connected to this variant
     * 
     * @param int $variantId
     * 
     * @return bool
     */
    public function deleteVariant(int $variantId): bool;
    
    /**
     * Delete an existing variant.
     * 
     * This will also affect all the items that are connected to this variant
     * 
     * @param int $elementId
     * 
     * @return bool
     */
    public function deleteElements(int $elementId): bool;
}