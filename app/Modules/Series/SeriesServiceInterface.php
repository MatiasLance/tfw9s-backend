<?php

namespace App\Modules\Series;

use App\Models\Series;
use App\Models\User;
use DateTime;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface SeriesServiceInterface
{
    /**
     * Retrieve a list of series
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Series>
     */
    public function listSeries(array $filters = []): Paginate;

    /**
     * Retrieve an Series
     *
     * @param int $id
     *
     * @return Series
     */
    public function retrieveSeries(int $id): Series;

    /**
     * Create a new Series
     *
     * @param string $name
     * @param string $type
     * @param string $description
     * @param string $address
     * @param int DateTime $start
     * @param int DateTime $end
     * @param float $price
     *
     * @return Series
     */
    public function createSeries(string $name, string $type, string $description, string $address, DateTime $start, DateTime $end, float $price, ?array $media, string $coachEmail, ?int $ageGroup): Series;

    /**
     * Update an existing Series
     *
     * @param int $id
     * @param string $name
     * @param string type
     * @param string $description
     * @param string $address
     * @param int DateTime $start
     * @param int DateTime $end
     * @param float $price
     *
     * @return bool
     */
    public function updateSeries(int $id, string $name, string $type, string $description, string $address, DateTime $start, DateTime $end, float $price, ?array $media, string $coachEmail, ?int $ageGroup): bool;

    /**
     * Delete an existing Series
     *
     * @param User $initiator The user who initiated the delete command
     * @param Series $series The series to be deleted
     *
     * @return bool
     */
    public function deleteSeries(User $initiator, Series $series): bool;

    /**
     * Update an existing Series
     *
     * @param int $id
     *
     * @return bool
     */
    public function resumeSeries(int $id): bool;

    /**
     * Update an existing Series
     *
     * @param int $id
     *
     * @return bool
     */
    public function pauseSeries(int $id): bool;

    /**
     * Update an existing Thumbnail
     *
     * @param array $media
     *
     * @return bool
     */
    public function editThumbnail(?array $media): bool;

}
