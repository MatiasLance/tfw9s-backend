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
        $region = $request->query('region', null);
        $agegroup = $request->query('agegroup', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_event_per_page' => $maxEventsPerPage,
            'event_date' => $eventDate,
            'event' => $event,
            'year' => $year,
            'manager' => $manager,
            'region' => $region,
            'agegroup' => $agegroup,
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

        $time = $request->input('time');
        $round = $request->input('round');
        $region_id = $request->input('region_id');
        $agegroup_id = $request->input('agegroup_id');
        $series_id = $request->input('series_id');
        $datetimeString = $request->input('datetime');
        $matches = $request->input('matches') ?? [];

        /*
        $name = $request->input('name') ?? '';
        $description = $request->input('description') ?? '';
        #submit datetime as string
        $datetimeString = $request->input('datetime');
        $manager_id = $request->input('manager_id');
        $agegroup_id = $request->input('agegroup_id');
        $series = $request->input('series');
        $teamcount = $request->input('teamcount');
        */

        $datetime = new DateTime($datetimeString);

        $event = $this->eventService->createEvent($time, $round, $region_id, $agegroup_id, $series_id, $datetime, $matches);

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
        $time = $request->input('time');
        $round = $request->input('round');
        $region_id = $request->input('region_id');
        $agegroup_id = $request->input('agegroup_id');
        $datetimeString = $request->input('datetime');
        $matches = $request->input('matches') ?? [];

        /*
        $name = $request->input('name') ?? '';
        $description = $request->input('description') ?? '';
        #submit datetime as string
        $manager_id = $request->input('manager_id');
        $series = $request->input('series');
        $teamcount = $request->input('teamcount');
        */

        $datetime = new DateTime($datetimeString);

        $isSuccess = $this->eventService->updateEvent($id, $time, $round, $region_id, $agegroup_id, $datetime, $matches);

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

    public function all(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxEventsPerPage = $request->query('maxEventsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_event_per_page' => $maxEventsPerPage,
        ];

        $events = $this->eventService->allEvents($filter);

        $message->setContent(200, 'Events retrieved', '', $events->toArray());

        return $message->render();
    }
}



