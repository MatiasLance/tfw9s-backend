<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Field\FieldServiceInterface;
use Illuminate\Http\Request;
use App\Models\Field;

class FieldController extends Controller
{
    protected FieldServiceInterface $fieldService;

    public function __construct(FieldServiceInterface $fieldService)
    {
        $this->fieldService = $fieldService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxFieldsPerPage = $request->query('maxFieldsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_field_per_page' => $maxFieldsPerPage,
        ];

        $fields = $this->fieldService->listFields($filter);

        $message->setContent(200, 'Fields retrieved', '', $fields->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $field = $this->fieldService->retrieveField($id);

        $message->setContent(200, 'Field retrieved', '', [
            'field' => $field
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $region_id = $request->input('region_id');

        $field = $this->fieldService->createField($name, $description, $region_id);

        if ($field instanceof Field) {
            $message->setContent(201, 'Field created', '', [
                'field' => $field
            ]);
        } else {
            $message->setContent(400, 'Field not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $region_id = $request->input('region_id');

        $isSuccess = $this->fieldService->updateField($id, $name, $description, $region_id);

        if ($isSuccess) {
            $message->setContent(200, 'Field updated');
        } else {
            $message->setContent(400, 'Field not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $field = $this->fieldService->retrieveField($id);

        $isSuccess = $this->fieldService->deleteField($user, $field);

        if ($isSuccess) {
            $message->setContent(200, 'Field deleted');
        } else {
            $message->setContent(400, 'Field not updated');
        }

        return $message->render();
    }
}


