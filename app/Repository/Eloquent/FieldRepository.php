<?php

namespace App\Repository\Eloquent;

use App\Models\Field;
use App\Modules\Field\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\FieldRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class FieldRepository extends BaseRepository implements FieldRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of fields
     *
     * @var array $defaultFieldListFilters
     */
    protected array $defaultFieldListFilters = [
        /**
         * Search keyword
         * This filters the fields with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the fields according to this value. By default, will sort the fields by their creation date.
         * For the available sort values, check App\Modules\Field\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of fields to get
         */
        'page' => 1,

        /**
         * Max field per page
         *
         * Maximum number of fields shown per page. When 0 or null is passed, will get every field
         */
        'max_field_per_page' => self::MAX_PAGE_FIELDS,
    ];

    public function __construct(Field $field, StorageInterface $storageService)
    {
        parent::__construct($field);
        $this->storageService = $storageService;
    }

    public function listFields(array $userFilters = []): Paginate
    {
        $fields = $this->model->query();

        $filters = array_merge($this->defaultFieldListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $fields = $fields->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $fields = $fields->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $fields = $fields->orderByDesc('name');
                break;

            default:
                $fields = $fields->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_field_per_page']) ? $fields->count() : $filters['max_field_per_page'];

        return new Paginate($fields, $maxPerPage, $filters['page'], 'fields');
    }

    public function retrieveField(int $id): field
    {
        return $this->find($id);
    }

    public function createField(string $name, string $description, int $region_id): field
    {
        $field = new Field();
        $field->name = $name;
        $field->description = $description;
        $field->region_id = $region_id;

        return DB::transaction(function() use($field) {
            $field->save();

            return $field;
        });
    }

    public function updateField(int $id, string $name, string $description, int $region_id): bool
    {
        $field = $this->find($id);
        $field->name = $name;
        $field->description = $description;
        $field->region_id = $region_id;

        return DB::transaction(function() use($field) {

            return $field->save();
        });
    }

    public function deleteField(int $id): bool
    {
        $field = $this->find($id);

        return DB::transaction(function() use($field) {

            return $field->delete();
        });
    }
}
