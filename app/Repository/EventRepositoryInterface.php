<?php

namespace App\Repository;

use App\Models\Event;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use DateTime;

interface EventRepositoryInterface
{
    /**
     * Maximum events to be shown per page
     *
     * @var int MAX_PAGE_EVENTS
     */
    public const MAX_PAGE_EVENTS = 12;

    /**
     * Placeholder event name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_event_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of events.
     *
     * @param array $userFilters
     *
     * @return Paginate<event>
     */
    public function listEvents(array $userFilters = []): Paginate;

    /**
     * Retrieve an event
     *
     * @param int $id
     *
     * @return event
     */
    public function retrieveEvent(int $id): Event;

    /**
     * Create a new event instance
     *
     * @param string $name
     * @param string $description
     * @param DateTime $datetime
     * @param int region_id
     * @param int manager_id
     * @param int agegroup_id
     * @param ?array $matches
     *
     * @return event
     */
    public function createEvent(string $name, string $description, DateTime $datetime, int $region_id, int $manager_id, int $agegroup_id, int $series, int $teamcount): Event;

    /**
     * Update an existing event instance
     *
     * @param int $id
     * @param string $name
     * @param string $description
     * @param DateTime $datetime
     * @param int region_id
     * @param int manager_id
     * @param int agegroup_id
     * @param ?array $matches
     *
     * @return bool
     */
    public function updateEvent(int $id, string $name, string $description, DateTime $datetime, int $region_id, int $manager_id, int $agegroup_id, int $series, int $teamcount, ?array $matches): bool;

    /**
     * Delete an existing event instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteEvent(int $id): bool;

    /**
     * Retrieve all of events.
     *
     * @param array $userFilters
     *
     * @return Paginate<event>
     */
    public function allEvents(array $userFilters = []): Paginate;

}
