<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Series\SeriesServiceInterface;
use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Team;
use DateTime;

class SeriesController extends Controller
{
    protected SeriesServiceInterface $seriesService;

    public function __construct(SeriesServiceInterface $seriesService)
    {
        $this->seriesService = $seriesService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $type = $request->query('type', null);
        $withFixing = $request->query('withFixing', null);
        $maxSeriesPerPage = $request->query('maxSeriesPerPage', null);
        $eventDate = $request->query('eventDate', null);
        $isPaused = $request->query('isPaused', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'type' => $type,
            'withFixing' => $withFixing,
            'max_series_per_page' => $maxSeriesPerPage,
            'event_date' => $eventDate,
            'is_paused' => $isPaused,
        ];

        $series = $this->seriesService->listSeries($filter);

        $message->setContent(200, 'Series retrieved', '', $series->toArray());

        return $message->render();
    }

    public function paginatedList(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $type = $request->query('type', null);
        $withFixing = $request->query('withFixing', null);
        $maxSeriesPerPage = $request->query('maxSeriesPerPage', null);
        $eventDate = $request->query('eventDate', null);
        $isPaused = $request->query('isPaused', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'type' => $type,
            'withFixing' => $withFixing,
            'max_series_per_page' => $maxSeriesPerPage,
            'event_date' => $eventDate,
            'is_paused' => $isPaused,
        ];

        $series = $this->seriesService->listOfSeries($filter);

        $message->setContent(200, 'Series retrieved', '', $series->toArray());

        return $message->render();
    }

    public function listOfSeriesName()
    {
        $series = Series::query()
            ->orderBy('name')
            ->get()
            ->map(function($series) {
                return [
                    'id' => $series->id,
                    'name' => $series->name,
                    'start' => $series->start,
                    'end' => $series->end
                ];
            });

        return response()->json([
            'series' => $series
        ]);
    }


    public function retrieve(Message $message, int $id)
    {
        $series = $this->seriesService->retrieveSeries($id);

        $message->setContent(200, 'Series retrieved', '', [
            'series' => $series
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $type = $request->input('type');
        $address = $request->input('address') ?? '';
        $startdatestring = $request->input('start');
        $enddatestring = $request->input('end');
        $price = $request->input('price');

        $start = new DateTime($startdatestring);
        $end = new DateTime($enddatestring);
        $media = $request->file('photo') ?? [];

        $series = $this->seriesService->createSeries($name, $type, $description, $address, $start, $end, $price, $media);

        if ($series instanceof Series) {
            $message->setContent(201, 'Series created', '', [
                'series' => $series
            ]);
        } else {
            $message->setContent(400, 'Series not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $type = $request->input('type');
        $address = $request->input('address') ?? '';
        $startdatestring = $request->input('start');
        $enddatestring = $request->input('end');
        $price = $request->input('price');

        $start = new DateTime($startdatestring);
        $end = new DateTime($enddatestring);

        $newPhoto = $request->file('photo') ?? [];
        $existingPhoto = $request->input('photo') ?? [];
        $newPhotoCount = count($newPhoto);
        $existingPhotoCount = count($existingPhoto);

        if (
            $request->has('photo') &&
            (
                $newPhotoCount > 0 ||
                $existingPhotoCount > 0
            )
        ) {
            foreach ($existingPhoto as $existingPhotoHash) {
                array_push($newPhoto, $existingPhotoHash);
            }
            $media = $newPhoto;
        } else {
            $media = null;
        }

        $isSuccess = $this->seriesService->updateSeries($id, $name, $type, $description, $address, $start, $end, $price, $media);

        if ($isSuccess) {
            $message->setContent(200, 'Series updated');
        } else {
            $message->setContent(400, 'Series not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $series = $this->seriesService->retrieveSeries($id);

        $isSuccess = $this->seriesService->deleteSeries($user, $series);

        if ($isSuccess) {
            $message->setContent(200, 'Series deleted');
        } else {
            $message->setContent(400, 'Series not updated');
        }

        return $message->render();
    }

    public function resumeSeries(Message $message, int $id)
    {

        $isSuccess = $this->seriesService->resumeSeries($id);

        if ($isSuccess) {
            $message->setContent(200, 'Series updated');
        } else {
            $message->setContent(400, 'Series not updated');
        }

        return $message->render();
    }

    public function pauseSeries(Message $message, int $id)
    {

        $isSuccess = $this->seriesService->pauseSeries($id);

        if ($isSuccess) {
            $message->setContent(200, 'Series updated');
        } else {
            $message->setContent(400, 'Series not updated');
        }

        return $message->render();
    }

    public function editThumbnail(Request $request, Message $message)
    {
        $media = $request->file('photo') ?? [];

        $isSuccess = $this->seriesService->editThumbnail($media);

        if ($isSuccess) {
            $message->setContent(200, 'Thumbnail updated');
        } else {
            $message->setContent(400, 'Thumbnail not updated');
        }

        return $message->render();
    }

    public function sendRegistration(Message $message, int $id)
    {

        $isSuccess = $this->seriesService->sendRegistrations($id);

        if ($isSuccess) {
            $message->setContent(200, 'Series team coaches notified');
        } else {
            $message->setContent(400, 'Series coaches notification failed');  
        }

        return $message->render();
    }

    public function seriesTeamLinks(Message $message, int $id)
    {

        $teamLinks = $this->seriesService->seriesTeamLinks($id);

        if (is_array($teamLinks)) {
            $message->setContent(201, 'Team links generated', '', [
                'teamLinks' => $teamLinks
            ]);
        } else {
            $message->setContent(400, 'Team links generation failed');  
        }

        return $message->render();
    }

    public function decrypt(Message $message, string $key)
    {

        $payload = decrypt($key);

        $series = Series::find($payload['series']);
        $team = Team::find($payload['team']);

        $message->setContent(200, 'Token decrypted...', '', [
            'series' => $series,
            'team' => $team
        ]);

        return $message->render();
    }
}


