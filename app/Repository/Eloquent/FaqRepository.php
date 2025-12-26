<?php

namespace App\Repository\Eloquent;

use App\Models\Faq;
use App\Modules\Faq\Filter;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\FaqRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;

class FaqRepository extends BaseRepository implements FaqRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of faq
     *
     * @var array $defaultFaqListFilters
     */
    protected array $defaultFaqListFilters = [
        /**
         * Search keyword
         * This filters the faq with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the faq according to this value. By default, will sort the faq by their creation date.
         * For the available sort values, check App\Modules\Faq\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of faq to get
         */
        'page' => 1,

        /**
         * Max faq per page
         *
         * Maximum number of faq shown per page. When 0 or null is passed, will get every faq
         */
        'max_faq_per_page' => self::MAX_PAGE_FAQ,
    ];

    public function __construct(Faq $faq, StorageInterface $storageService)
    {
        parent::__construct($faq);
        $this->storageService = $storageService;
    }

    public function listFaq(array $userFilters = []): Paginate
    {
        $faq = $this->model->query();

        $filters = array_merge($this->defaultFaqListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $faq = $faq->where('title', 'like', '%' . $filters['q'] . '%');
        }   

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $faq = $faq->orderBy('title');
                break;

            case Filter::SORT_Z_TO_A:
                $faq = $faq->orderByDesc('title');
                break;

            default:
                $faq = $faq->orderBy('created_at');
                break;
        }

        return new Paginate($faq, $filters['max_faq_per_page'], $filters['page'], 'faq');
    }

    public function retrieveFaq(int $id): Faq
    {
        return $this->find($id);
    }

    public function store(string $title, string $description): Faq
    {
        $faq = new Faq();
        $faq->title = $title;
        $faq->description = $description;

        return DB::transaction(function() use($faq) {
            $faq->save();
            return $faq;
        });
        
    }

    public function updateFaq(int $id, string $title, string $description): bool
    {
        $faq = $this->find($id);
        $faq->title = $title;
        $faq->description = $description;

        return DB::transaction(function() use($faq) {
            return $faq->save();
        });
    }

    public function deleteFaq(int $id): bool
    {
        $faq = $this->find($id);

        return DB::transaction(function() use($faq) {

            return $faq->delete();
        });
    }


}
