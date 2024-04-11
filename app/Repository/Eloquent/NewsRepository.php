<?php

namespace App\Repository\Eloquent;

use App\Models\News;
use App\Modules\News\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\NewsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class NewsRepository extends BaseRepository implements NewsRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of newss
     *
     * @var array $defaultNewsListFilters
     */
    protected array $defaultNewsListFilters = [
        /**
         * Search keyword
         * This filters the newss with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the newss according to this value. By default, will sort the newss by their creation date.
         * For the available sort values, check App\Modules\News\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of newss to get
         */
        'page' => 1,

        /**
         * Max news per page
         *
         * Maximum number of newss shown per page. When 0 or null is passed, will get every news
         */
        'max_news_per_page' => self::MAX_PAGE_NEWS,
    ];

    public function __construct(News $news, StorageInterface $storageService)
    {
        parent::__construct($news);
        $this->storageService = $storageService;
    }

    public function listNews(array $userFilters = []): Paginate
    {
        $news = $this->model->query();

        $filters = array_merge($this->defaultNewsListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $news = $news->where(function ($q) use($filters) {
                $q
                    ->where('headline', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $news = $news->orderBy('headline');
                break;

            case Filter::SORT_Z_TO_A:
                $news = $news->orderByDesc('headline');
                break;

            default:
                $news = $news->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_news_per_page']) ? $news->count() : $filters['max_news_per_page'];

        return new Paginate($news, $maxPerPage, $filters['page'], 'news');
    }

    public function retrieveNews(int $id): News
    {
        return $this->find($id);
    }

    public function createNews(string $headline, string $content, array $image): News
    {
        $news = new News();
        $news->headline = $headline;
        $news->content = $content;

        return DB::transaction(function() use($news, $image) {
            $news->save();

            foreach ($image as $file) {
              if (!is_null($file)) {

                  $newsImage = $this->storageService->store($file);
                  $news->images()->save($newsImage);
              }
            }

            return $news;
        });
    }

    public function updateNews(int $id, string $headline, string $lead, string $body): bool
    {
        $news = $this->find($id);
        $news->headline = $headline;
        $news->lead = $lead;
        $news->body = $body;

        return DB::transaction(function() use($news) {

            return $news->save();
        });
    }

    public function deleteNews(int $id): bool
    {
        $news = $this->find($id);

        return DB::transaction(function() use($news) {

            return $news->delete();
        });
    }
}
