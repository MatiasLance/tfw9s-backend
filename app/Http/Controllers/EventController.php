<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Event\EventServiceInterface;
use Illuminate\Http\Request;
use App\Models\Event;
use DateTime;

class EventController extends Controller
{
    protected EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxEventsPerPage = $request->query('maxEventsPerPage', null);
        $eventDate = $request->query('eventDate', null);
        $event = $request->query('event', null);
        $year = $request->query('year', null);
        $manager = $request->query('manager', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_event_per_page' => $maxEventsPerPage,
            'event_date' => $eventDate,
            'event' => $event,
            'year' => $year,
            'manager' => $manager,
        ];

        $events = $this->eventService->listEvents($filter);

        $message->setContent(200, 'Events retrieved', '', $events->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $event = $this->eventService->retrieveEvent($id);

        $message->setContent(200, 'Event retrieved', '', [
            'event' => $event
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $name = $request->input('name') ?? '';
        $description = $request->input('description') ?? '';

        #submit datetime as string
        $datetimeString = $request->input('datetime');
        $field_id = $request->input('field_id');
        $manager_id = $request->input('manager_id');
        $agegroup_id = $request->input('agegroup_id');
        $matches = $request->input('matches') ?? [];

        $datetime = new DateTime($datetimeString);

        $event = $this->eventService->createEvent($name, $description, $datetime, $field_id, $manager_id, $agegroup_id, $matches);

        if ($event instanceof Event) {
            $message->setContent(201, 'Event created', '', [
                'event' => $event
            ]);
        } else {
            $message->setContent(400, 'Event not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $name = $request->input('name') ?? '';
        $description = $request->input('description') ?? '';

        #submit datetime as string
        $datetimeString = $request->input('datetime');
        $field_id = $request->input('field_id');
        $manager_id = $request->input('manager_id');
        $agegroup_id = $request->input('agegroup_id');
        $matches = $request->input('matches') ?? [];

        $datetime = new DateTime($datetimeString);

        $isSuccess = $this->eventService->updateEvent($id, $name, $description, $datetime, $field_id, $manager_id, $agegroup_id, $matches);

        if ($isSuccess) {
            $message->setContent(200, 'Event updated');
        } else {
            $message->setContent(400, 'Event not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $event = $this->eventService->retrieveEvent($id);

        $isSuccess = $this->eventService->deleteEvent($user, $event);

        if ($isSuccess) {
            $message->setContent(200, 'Event deleted');
        } else {
            $message->setContent(400, 'Event not updated');
        }

        return $message->render();
    }
}



