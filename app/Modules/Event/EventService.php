<?php

namespace App\Modules\Event;

use App\Models\User;
use App\Models\Event;
use App\Modules\Utility\Pagination\Paginate;
use DateTime;
use App\Repository\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventService implements EventServiceInterface
{
    /**
     * Event Repository
     *
     * @var EventRepositoryInterface $eventRepository
     */
    protected EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function listEvents(array $filters = []): Paginate
    {
        return $this->eventRepository->listEvents($filters);
    }

    public function retrieveEvent(int $id): Event
    {
        return $this->eventRepository->retrieveEvent($id);
    }

    public function createEvent(string $time, string $round, int $region_id, int $agegroup_id ,  int $series_id, DateTime $datetime, array $matches): Event
    {
        return $this->eventRepository->createEvent($time, $round, $region_id, $agegroup_id, $series_id, $datetime, $matches);
    }

    public function updateEvent(int $id, string $time, string $round, int $region_id, int $agegroup_id, DateTime $datetime, array $matches): bool
    {
        return $this->eventRepository->updateEvent($id, $time, $round, $region_id, $agegroup_id, $datetime, $matches);
    }

    public function deleteEvent(User $initiator, Event $event): bool
    {
        return $this->eventRepository->deleteEvent($event->id);
    }

    public function allEvents(array $filters = []): Paginate
    {
        return $this->eventRepository->allEvents($filters);
    }
}
