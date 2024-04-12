<?php

namespace App\Modules\News;

use App\Models\News;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface NewsServiceInterface
{
    /**
     * Retrieve a list of news
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<News>
     */
    public function listNews(array $filters = []): Paginate;

    /**
     * Retrieve an News
     *
     * @param int $id
     *
     * @return News
     */
    public function retrieveNews(int $id): News;

    /**
     * Create a new News
     *
     * @param string $headline
     * @param string $content
     * @param array|null<UploadedFile> $media
     *
     * @return News
     */
    public function createNews(string $headline, string $content, ?array $media): News;

    /**
     * Update an existing News
     *
     * @param string $headline
     * @param string $content
     * @param array|null<UploadedFile> $media
     */
    public function updateNews(int $id, string $headline, string $content, ?array $media): bool;

    /**
     * Delete an existing News
     *
     * @param User $initiator The user who initiated the delete command
     * @param News $news The news to be deleted
     *
     * @return bool
     */
    public function deleteNews(User $initiator, News $news): bool;

}
