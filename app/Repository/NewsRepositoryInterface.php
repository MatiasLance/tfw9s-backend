<?php

namespace App\Repository;

use App\Models\News;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface NewsRepositoryInterface
{
    /**
     * Maximum newss to be shown per page
     *
     * @var int MAX_PAGE_NEWS
     */
    public const MAX_PAGE_NEWS = 12;

    /**
     * Placeholder news name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_news_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of newss.
     *
     * @param array $userFilters
     *
     * @return Paginate<news>
     */
    public function listNews(array $userFilters = []): Paginate;

    /**
     * Retrieve an news
     *
     * @param int $id
     *
     * @return news
     */
    public function retrieveNews(int $id): News;

    /**
     * Create a new news instance
     *
     * @param string $headline
     * @param string $content
     * @param array|null<UploadedFile> $image
     *
     * @return news
     */
    public function createNews(string $headline, string $content, array $image): News;

    /**
     * Update an existing news instance
     *
     * @param int $id
     * @param string $headline
     * @param string $lead
     * @param string $body
     *
     * @return bool
     */
    public function updateNews(int $id, string $headline, string $lead, string $body): bool;

    /**
     * Delete an existing news instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteNews(int $id): bool;

}
