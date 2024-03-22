<?php

namespace App\Repository;

use App\Models\Field;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface FieldRepositoryInterface
{
    /**
     * Maximum fields to be shown per page
     *
     * @var int MAX_PAGE_fieldS
     */
    public const MAX_PAGE_FIELDS = 12;

    /**
     * Placeholder field name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_field_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of fields.
     *
     * @param array $userFilters
     *
     * @return Paginate<field>
     */
    public function listfields(array $userFilters = []): Paginate;

    /**
     * Retrieve an field
     *
     * @param int $id
     *
     * @return field
     */
    public function retrievefield(int $id): Field;

    /**
     * Create a new field instance
     *
     * @param string $name
     * @param string $description
     * @param int $region_id
     *
     * @return field
     */
    public function createfield(string $name, string $description, int $region_id): Field;

    /**
     * Update an existing field instance
     *
     * @param int $id
     * @param string $name
     * @param string $description
     * @param int $region_id
     *
     * @return bool
     */
    public function updatefield(int $id, string $name, string $description, int $region_id): bool;

    /**
     * Delete an existing field instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deletefield(int $id): bool;

}
