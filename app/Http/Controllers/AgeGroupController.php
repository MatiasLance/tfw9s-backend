<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\AgeGroup\AgeGroupServiceInterface;
use Illuminate\Http\Request;
use App\Models\AgeGroup;

class AgeGroupController extends Controller
{
    protected AgeGroupServiceInterface $ageGroupService;

    public function __construct(AgeGroupServiceInterface $ageGroupService)
    {
        $this->ageGroupService = $ageGroupService;
    }

    public function test(){
        return 'AgeGroup Controller have been contacted';
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxAgeGroupsPerPage = $request->query('maxAgeGroupsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_age_group_per_page' => $maxAgeGroupsPerPage,
        ];

        $ageGroup = $this->ageGroupService->listAgeGroup($filter);

        $message->setContent(200, 'Age Group retrieved', '', $ageGroup->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $ageGroup = $this->ageGroupService->retrieveAgeGroup($id);

        $message->setContent(200, 'Age Group retrieved', '', [
            'ageGroup' => $ageGroup
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $name = $request->input('name');
        $min_age = $request->input('min_age');
        $max_age = $request->input('max_age');

        $ageGroup = $this->ageGroupService->createAgeGroup($name, $min_age, $max_age);

        if ($ageGroup instanceof AgeGroup) {
            $message->setContent(201, 'AgeGroup created', '', [
                'ageGroup' => $ageGroup
            ]);
        } else {
            $message->setContent(400, 'AgeGroup not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $name = $request->input('name');
        $min_age = $request->input('min_age');
        $max_age = $request->input('max_age');

        $isSuccess = $this->ageGroupService->updateAgeGroup($id, $name, $min_age, $max_age);

        if ($isSuccess) {
            $message->setContent(200, 'AgeGroup updated');
        } else {
            $message->setContent(400, 'AgeGroup not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $ageGroup = $this->ageGroupService->retrieveAgeGroup($id);

        $isSuccess = $this->ageGroupService->deleteAgeGroup($user, $ageGroup);

        if ($isSuccess) {
            $message->setContent(200, 'AgeGroupdeleted');
        } else {
            $message->setContent(400, 'AgeGroupnot updated');
        }

        return $message->render();
    }
}


