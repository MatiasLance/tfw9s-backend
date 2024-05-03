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
     *
     * @return Series
     */
    public function createSeries(string $name, string $type, string $description, string $address, DateTime $start, DateTime $end): Series;

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
     * 
     * @return bool
     */
    public function updateSeries(int $id, string $name, string $type, string $description, string $address, DateTime $start, DateTime $end): bool;

    /**
     * Delete an existing Series
     *
     * @param User $initiator The user who initiated the delete command
     * @param Series $series The series to be deleted
     *
     * @return bool
     */
    public function deleteSeries(User $initiator, Series $series): bool;

}
