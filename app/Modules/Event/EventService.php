<?php

namespace App\Modules\Event;

use App\Models\User;
use App\Models\Event;
use App\Modules\Utility\Pagination\Paginate;
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

    public function createEvent($name, $description, $datetime, $field_id): Event
    {
        return $this->eventRepository->createEvent($name, $description, $datetime, $field_id);
    }

    public function updateEvent(int $id, string $name, string $description, $datetime, $field_id): bool
    {
        return $this->eventRepository->updateEvent($id, $name, $description, $datetime, $field_id);
    }

    public function deleteEvent(User $initiator, Event $event): bool
    {
        return $this->eventRepository->deleteEvent($event->id);
    }
}
