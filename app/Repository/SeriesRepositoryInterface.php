<?php

namespace App\Repository;

use App\Models\Series;
use DateTime;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface SeriesRepositoryInterface
{
    /**
     * Maximum series to be shown per page
     *
     * @var int MAX_PAGE_TEAMS
     */
    public const MAX_PAGE_TEAMS = 12;

    /**
     * Placeholder series name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_series_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of series.
     *
     * @param array $userFilters
     *
     * @return Paginate<series>
     */
    public function listSeries(array $userFilters = []): Paginate;

    /**
     * Retrieve an series
     *
     * @param int $id
     *
     * @return series
     */
    public function retrieveSeries(int $id): Series;

    /**
     * Create a new series instance
     *
     * @param string $name
     * @param string $type
     * @param string $description
     * @param string $address
     * @param int DateTime $start
     * @param int DateTime $end
     * @param float $price
     *
     * @return series
     */
    public function createSeries(string $name, string $type, string $description, string $address, DateTime $start, DateTime $end, float $price, ?array $media): Series;

    /**
     * Update an existing series instance
     *
     * @param int $id
     * @param string $name
     * @param string $type
     * @param string $description
     * @param string $address
     * @param int DateTime $start
     * @param int DateTime $end
     * @param float $price
     *
     * @return bool
     */
    public function updateSeries(int $id, string $name, string $type, string $description, string $address, DateTime $start, DateTime $end, float $price, ?array $media): bool;

    /**
     * Delete an existing series instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteSeries(int $id): bool;

}
