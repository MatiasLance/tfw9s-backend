<?php

namespace App\Modules\Event;

use App\Models\Event;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

interface EventServiceInterface
{
    /**
     * Retrieve a list of events
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Event>
     */
    public function listEvents(array $filters = []): Paginate;

    /**
     * Retrieve an Event
     *
     * @param int $id
     *
     * @return Event
     */
    public function retrieveEvent(int $id): Event;

    /**
     * Create a new Event
     *
     * @param string $time
     * @param int region_id
     * @param int agegroup_id
     * @param DateTime $datetime
     * @param array matches
     *
     * @return Event
     */
    public function createEvent(string $time, int $region_id, int $agegroup_id, DateTime $datetime, array $matches): Event;

    /**
     * Update an existing Event
     *
     * @param int $id
     * @param string $time
     * @param int region_id
     * @param int agegroup_id
     * @param int DateTime $datetime
     * @param ?array matches
     *
     * @return bool
     */
    public function updateEvent(int $id, string $time, int $region_id, int $agegroup_id, DateTime $datetime, array $matches): bool;

    /**
     * Delete an existing Event
     *
     * @param User $initiator The user who initiated the delete command
     * @param Event $event The event to be deleted
     *
     * @return bool
     */
    public function deleteEvent(User $initiator, Event $event): bool;

    /**
     * Retrieve a list of events
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Event>
     */
    public function allEvents(array $filters = []): Paginate;
}
