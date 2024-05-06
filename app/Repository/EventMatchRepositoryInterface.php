<?php

namespace App\Repository;

use App\Models\EventMatch;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface EventMatchRepositoryInterface
{
    /**
     * Maximum eventMatches to be shown per page
     *
     * @var int MAX_PAGE_EVENTMATCHES
     */
    public const MAX_PAGE_EVENTMATCHES = 12;

    /**
     * Placeholder eventMatch name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_eventMatch_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of eventMatches.
     *
     * @param array $userFilters
     *
     * @return Paginate<eventMatch>
     */
    public function listEventMatches(array $userFilters = []): Paginate;

    /**
     * Retrieve an eventMatch
     *
     * @param int $id
     *
     * @return eventMatch
     */
    public function retrieveEventMatch(int $id): EventMatch;

    /**
     * Create a new eventMatch instance
     *
     * @param int $event_id
     * @param int $field_id
     * @param string $match_time
     * @param int $team1
     * @param int $team2
     *
     * @return eventMatch
     */
    public function createEventMatch(int $event_id, int $field_id, string $match_time, int $team1, int $team2): EventMatch;

    /**
     * Update an existing eventMatch instance
     *
     * @param int $id
     * @param int $event_id
     * @param int $field_id
     * @param string $match_time
     * @param int $team1
     * @param int $team2
     *
     * @return bool
     */
    public function updateEventMatch(int $id, int $event_id, int $field_id, string $match_time, int $team1, int $team2): bool;

        /**
     * Update an existing eventMatch score
     *
     * @param int $id
     * @param int $team1_score
     * @param int $team2_score
     *
     * @return bool
     */
    public function updateEventMatchScore(int $id, int $team1_score, int $team2_score): bool;

    /**
     * Update an existing eventMatch instance
     *
     * @param int $id
     * @param int $team1_score
     * @param int $team2_score
     *
     * @return bool
     */
    public function storeResult(int $id, int $team1_score, int $team2_score): bool;


    /**
     * Delete an existing eventMatch instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteEventMatch(int $id): bool;


    /**
     * Upload an EventMatch Video
     *
     * @param int $id
     * @param UploadedFile $video
     *
     * @return bool
     */
    public function addVideo(int $id, UploadedFile $video): bool;
}
