<?php

namespace App\Modules\EventMatch;

use App\Models\EventMatch;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

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
     * @param int $field_id
     * @param int $team1
     * @param int $team2
     *
     * @return EventMatch
     */
    public function createEventMatch(int $event_id, int $field_id, int $team1, int $team2): EventMatch;

    /**
     * Update an existing EventMatch
     *
     * @param int $id
     * @param int $event_id
     * @param int $field_id
     * @param int $team1
     * @param int $team2
     *
     * @return bool
     */
    public function updateEventMatch(int $id, int $event_id, int $field_id, int $team1, int $team2): bool;

        /**
     * Update an existing EventMatch score
     *
     * @param int $id
     * @param int $team1_score
     * @param int $team2_score
     *
     * @return bool
     */
    public function updateEventMatchScore(int $id, int $team1_score, int $team2_score, bool $isAbandonedMatch): bool;

    /**
     * Create a new EventMatch
     *
     * @param int $id
     * @param int $team1_score
     * @param int $team2_score
     *
     * @return EventMatch
     */
    public function storeResult(int $id, int $team1_score, int $team2_score): bool;

    /**
     * Revert a submitted result and rebuild the affected standings.
     */
    public function revertResult(int $id): bool;

    /**
     * Delete an existing EventMatch
     *
     * @param User $initiator The user who initiated the delete command
     * @param EventMatch $eventMatch The field to be deleted
     *
     * @return bool
     */
    public function deleteEventMatch(User $initiator, EventMatch $eventMatch): bool;

    /**
     * Delete an existing EventMatch
     *
     * @param int $id
     * @param UploadedFile $video
     *
     * @return bool
     */
    public function addVideo(int $id, UploadedFile $video): bool;

}
