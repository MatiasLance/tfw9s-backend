<?php

namespace App\Modules\EventMatch;

use App\Models\EventMatch;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface EventMatchServiceInterface
{
    /**
     * Retrieve a list of fields
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<EventMatch>
     */
    public function listEventMatches(array $filters = []): Paginate;

    /**
     * Retrieve an EventMatch
     *
     * @param int $id
     *
     * @return EventMatch
     */
    public function retrieveEventMatch(int $id): EventMatch;

    /**
     * Create a new EventMatch
     *
     * @param int $event_id
     * @param string $match_time
     * @param int $team1
     * @param int $team2
     * @param int $team1_score
     * @param int $team2_score
     *
     * @return EventMatch
     */
    public function createEventMatch(int $event_id, string $match_time, int $team1, int $team2, int $team1_score, int $team2_score): EventMatch;

    /**
     * Update an existing EventMatch
     *
     * @param int $id
     * @param int $event_id
     * @param string $match_time
     * @param int $team1
     * @param int $team2
     * @param int $team1_score
     * @param int $team2_score
     *
     * @return bool
     */
    public function updateEventMatch(int $id, int $event_id, string $match_time, int $team1, int $team2, int $team1_score, int $team2_score): bool;

    /**
     * Delete an existing EventMatch
     *
     * @param User $initiator The user who initiated the delete command
     * @param EventMatch $eventMatch The field to be deleted
     *
     * @return bool
     */
    public function deleteEventMatch(User $initiator, EventMatch $eventMatch): bool;

}
