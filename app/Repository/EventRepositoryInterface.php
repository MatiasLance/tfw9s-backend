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
     * Create a new Event
     *
     * @param int DateTime $datetime
     * @param int region_id
     * @param int agegroup_id
     * @param ?array matches
     *
     * @return Event
     */
    public function createEvent(DateTime $datetime, int $region_id, int $agegroup_id, array $matches): Event;

    /**
     * Update an existing event instance
     *
     * @param int $id
     * @param int DateTime $datetime
     * @param int region_id
     * @param int agegroup_id
     * @param ?array matches
     *
     * @return bool
     */
    public function updateEvent(int $id, DateTime $datetime, int $region_id, int $agegroup_id, array $matches): bool;

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
