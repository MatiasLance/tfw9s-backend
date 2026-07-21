<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Modules\Event\EventServiceInterface;
use App\Modules\Http\Message;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $eventDate = $request->query('event_date', null);
        $event = $request->query('event', null);
        $year = $request->query('year', null);
        $manager = $request->query('manager', null);
        $region = $request->query('region', null);
        $agegroup = $request->query('agegroup', null);
        $seriesName = $request->query('series_name', null);
        $isSubmitted = $request->query('submit', false);

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
            'series_name' => $seriesName,
            'is_submitted' => $isSubmitted,
        ];

        $events = $this->eventService->listEvents($filter);

        $message->setContent(200, 'Events retrieved', '', $events->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $event = $this->eventService->retrieveEvent($id);

        $message->setContent(200, 'Event retrieved', '', [
            'event' => $event,
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $validated = $this->validateEvent($request, true);
        $matches = $validated['matches'] ?? [];
        $datetime = new DateTime($validated['datetime']);

        $event = $this->eventService->createEvent(
            $validated['time'],
            $validated['round'],
            (int) $validated['region_id'],
            (int) $validated['agegroup_id'],
            (int) $validated['series_id'],
            $datetime,
            $matches
        );

        if ($event instanceof Event) {
            $message->setContent(201, 'Event created', '', [
                'event' => $event,
            ]);
        } else {
            $message->setContent(400, 'Event not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $validated = $this->validateEvent($request, false);
        $matches = $validated['matches'] ?? [];
        $datetime = new DateTime($validated['datetime']);

        $isSuccess = $this->eventService->updateEvent(
            $id,
            $validated['time'],
            $validated['round'],
            (int) $validated['region_id'],
            (int) $validated['agegroup_id'],
            $datetime,
            $matches
        );

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

    private function validateEvent(Request $request, bool $requireSeries): array
    {
        $rules = [
            'time' => ['required', 'date_format:H:i'],
            'round' => ['required', 'string', 'max:50'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'agegroup_id' => ['required', 'integer', 'exists:age_groups,id'],
            'datetime' => ['required', 'date_format:Y-m-d'],
            'matches' => ['nullable', 'array'],
            'matches.*.id' => ['nullable', 'integer'],
            'matches.*.field_id' => ['required', 'integer', 'distinct', 'exists:fields,id'],
            'matches.*.team1' => ['required', 'integer', 'min:1', 'exists:teams,id'],
            'matches.*.team2' => ['required', 'integer', 'min:0'],
        ];

        if ($requireSeries) {
            $rules['series_id'] = ['required', 'integer', 'exists:series,id'];
        }

        $validated = $request->validate($rules);
        $scheduledTeams = [];

        foreach ($validated['matches'] ?? [] as $index => $match) {
            $team1 = (int) $match['team1'];
            $team2 = (int) $match['team2'];

            if ($team2 > 0 && ! DB::table('teams')->where('id', $team2)->whereNull('deleted_at')->exists()) {
                throw ValidationException::withMessages([
                    "matches.{$index}.team2" => 'The selected Team 2 is invalid.',
                ]);
            }

            if ($team2 > 0 && $team1 === $team2) {
                throw ValidationException::withMessages([
                    "matches.{$index}.team2" => 'A team cannot play against itself.',
                ]);
            }

            foreach (array_filter([$team1, $team2]) as $teamId) {
                if (isset($scheduledTeams[$teamId])) {
                    throw ValidationException::withMessages([
                        "matches.{$index}.team1" => 'A team cannot be scheduled in two fields at the same time.',
                    ]);
                }
                $scheduledTeams[$teamId] = true;
            }
        }

        return $validated;
    }
}
