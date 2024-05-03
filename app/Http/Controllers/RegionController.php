<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Region\RegionServiceInterface;
use Illuminate\Http\Request;
use App\Models\Region;

class RegionController extends Controller
{
    protected RegionServiceInterface $regionService;

    public function __construct(RegionServiceInterface $regionService)
    {
        $this->regionService = $regionService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxRegionsPerPage = $request->query('maxRegionsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_region_per_page' => $maxRegionsPerPage,
        ];

        $regions = $this->regionService->listRegions($filter);

        $message->setContent(200, 'Regions retrieved', '', $regions->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $region = $this->regionService->retrieveRegion($id);

        $message->setContent(200, 'Region retrieved', '', [
            'region' => $region
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';

        $region = $this->regionService->createRegion($name, $description);

        if ($region instanceof Region) {
            $message->setContent(201, 'Region created', '', [
                'region' => $region
            ]);
        } else {
            $message->setContent(400, 'Region not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';

        $isSuccess = $this->regionService->updateRegion($id, $name, $description);

        if ($isSuccess) {
            $message->setContent(200, 'Region updated');
        } else {
            $message->setContent(400, 'Region not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $region = $this->regionService->retrieveRegion($id);

        $isSuccess = $this->regionService->deleteRegion($user, $region);

        if ($isSuccess) {
            $message->setContent(200, 'Region deleted');
        } else {
            $message->setContent(400, 'Region not updated');
        }

        return $message->render();
    }

    public function all(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxRegionsPerPage = $request->query('maxRegionsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_region_per_page' => $maxRegionsPerPage,
        ];

        $regions = $this->regionService->allRegions($filter);

        $message->setContent(200, 'Regions retrieved', '', $regions->toArray());

        return $message->render();
    }
}

