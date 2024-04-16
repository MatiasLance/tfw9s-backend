<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Manager\ManagerServiceInterface;
use Illuminate\Http\Request;
use App\Models\Manager;

class ManagerController extends Controller
{
    protected ManagerServiceInterface $managerService;

    public function __construct(ManagerServiceInterface $managerService)
    {
        $this->managerService = $managerService;
    }

    public function list(Request $request, Message $message)
    {
        $user = $request->query('user', null);
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxManagersPerPage = $request->query('maxManagersPerPage', null);

        $filter = [
            'user' => $user,
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_manager_per_page' => $maxManagersPerPage,
        ];

        $managers = $this->managerService->listManagers($filter);

        $message->setContent(200, 'Managers retrieved', '', $managers->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $manager = $this->managerService->retrieveManager($id);

        $message->setContent(200, 'Manager retrieved', '', [
            'manager' => $manager
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $user_id = $request->input('user_id');
        $date_of_birth = $request->input('date_of_birth');
        $address = $request->input('address');
        $age = $request->input('age');

        $manager = $this->managerService->createManager($user_id, $date_of_birth, $address, $age);

        if ($manager instanceof Manager) {
            $message->setContent(201, 'Manager created', '', [
                'manager' => $manager
            ]);
        } else {
            $message->setContent(400, 'Manager not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $user_id = $request->input('user_id');
        $date_of_birth = $request->input('date_of_birth');
        $address = $request->input('address');
        $age = $request->input('age');

        $isSuccess = $this->managerService->updateManager($id, $user_id, $date_of_birth, $address, $age);

        if ($isSuccess) {
            $message->setContent(200, 'Manager updated');
        } else {
            $message->setContent(400, 'Manager not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $manager = $this->managerService->retrieveManager($id);

        $isSuccess = $this->managerService->deleteManager($user, $manager);

        if ($isSuccess) {
            $message->setContent(200, 'Manager deleted');
        } else {
            $message->setContent(400, 'Manager not updated');
        }

        return $message->render();
    }
}


