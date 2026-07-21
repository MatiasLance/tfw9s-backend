<?php

namespace App\Modules\Series;

use App\Models\User;
use App\Models\Series;
use DateTime;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\SeriesRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SeriesService implements SeriesServiceInterface
{
    /**
     * Series Repository
     *
     * @var SeriesRepositoryInterface $seriesRepository
     */
    protected SeriesRepositoryInterface $seriesRepository;

    public function __construct(SeriesRepositoryInterface $seriesRepository)
    {
        $this->seriesRepository = $seriesRepository;
    }

    public function listSeries(array $filters = []): Paginate
    {
        return $this->seriesRepository->listSeries($filters);
    }

    public function listOfSeries(array $filters = []): Paginate
    {
        return $this->seriesRepository->listOfSeries($filters);
    }

    public function retrieveSeries(int $id, bool $includeTeams = true): Series
    {
        return $this->seriesRepository->retrieveSeries($id, $includeTeams);
    }

    public function createSeries($name, $type, $description, $address, $start, $end, $price, $media): Series
    {
        return $this->seriesRepository->createSeries($name, $type, $description, $address, $start, $end, $price, $media);
    }

    public function updateSeries(int $id, string $name, string $type, string $description, string $address, DateTime $start, DateTime $end, float $price, ?array $media): bool
    {
        return $this->seriesRepository->updateSeries($id, $name, $type, $description, $address, $start, $end, $price, $media);
    }

    public function deleteSeries(User $initiator, Series $series): bool
    {
        return $this->seriesRepository->deleteSeries($series->id);
    }

    public function resumeSeries(int $id): bool
    {
        return $this->seriesRepository->resumeSeries($id);
    }

    public function pauseSeries(int $id): bool
    {
        return $this->seriesRepository->pauseSeries($id);
    }

    public function editThumbnail(?array $media): bool
    {
        return $this->seriesRepository->editThumbnail($media);
    }

    public function sendRegistrations(int $id): bool
    {
        return $this->seriesRepository->sendRegistrations($id);
    }

    public function seriesTeamLinks(int $id): array
    {
        return $this->seriesRepository->seriesTeamLinks($id);
    }
}
