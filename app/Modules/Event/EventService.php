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

    public function createEvent(DateTime $datetime, int $region_id, int $agegroup_id , ?array $matches): Event
    {
        return $this->eventRepository->createEvent($datetime, $region_id, $agegroup_id, $matches);
    }

    public function updateEvent(int $id, DateTime $datetime, int $region_id, int $agegroup_id , ?array $matches): bool
    {
        return $this->eventRepository->updateEvent($id, $datetime, $region_id, $agegroup_id, $matches);
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
