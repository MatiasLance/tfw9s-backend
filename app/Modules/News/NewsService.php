<?php

namespace App\Modules\News;

use App\Models\User;
use App\Models\News;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\NewsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use DateTime;

class NewsService implements NewsServiceInterface
{
    /**
     * News Repository
     *
     * @var NewsRepositoryInterface $newsRepository
     */
    protected NewsRepositoryInterface $newsRepository;

    public function __construct(NewsRepositoryInterface $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    public function listNews(array $filters = []): Paginate
    {
        return $this->newsRepository->listNews($filters);
    }

    public function retrieveNews(int $id): News
    {
        return $this->newsRepository->retrieveNews($id);
    }

    public function createNews(string $headline, string $content, ?array $media): News
    {
        return $this->newsRepository->createNews($headline, $content, $media);
    }

    public function updateNews(int $id, string $headline, string $content, ?array $media): bool
    {
        return $this->newsRepository->updateNews($id, $headline, $content, $media);
    }

    public function deleteNews(User $initiator, News $news): bool
    {
        return $this->newsRepository->deleteNews($news->id);
    }
}
