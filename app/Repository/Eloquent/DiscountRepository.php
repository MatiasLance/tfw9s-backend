<?php

namespace App\Repository\Eloquent;

use App\Models\DiscountCode;
use App\Modules\Discount\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\DiscountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use DateTime;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInterface
{
     /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of series
     *
     * @var array $defaultDiscountListFilters
     */
    protected array $defaultDiscountListFilters = [
        /**
         * Search keyword
         * This filters the regions with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the regions according to this value. By default, will sort the regions by their creation date.
         * For the available sort values, check App\Modules\Region\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of regions to get
         */
        'page' => 1,

        /**
         * Max region per page
         *
         * Maximum number of regions shown per page. When 0 or null is passed, will get every region
         */
        'max_discount_per_page' => self::MAX_PAGE_DISCOUNT,
    ];

    public function __construct(DiscountCode $discount)
    {
        parent::__construct($discount);
    }

    public function listDiscount(array $userFilters = []): Paginate
    {
        $discount = $this->model->query();

        $filters = array_merge($this->defaultDiscountListFilters, array_filter($userFilters, fn($f) => !is_null($f)));

         if (!is_null($filters['q'])) {
            $discount = $discount->where(function ($q) use($filters) {
                $q
                    ->where('code', 'LIKE', '%' . $filters['q'] . '%');
            });
        }
             
        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $discount = $discount->orderBy('code');
                break;
            case Filter::SORT_Z_TO_A:
                $discount = $discount->orderByDesc('code');
                break;
            default:
                $discount = $discount->orderBy('created_at'); 
                break;
        }
        
        $discount = $discount->with('teams');

        return new Paginate($discount, $filters['max_discount_per_page'], $filters['page'], 'discount');
    }
}