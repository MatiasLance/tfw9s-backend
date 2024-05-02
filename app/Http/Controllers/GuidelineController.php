<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Guideline\GuidelineServiceInterface;
use Illuminate\Http\Request;
use App\Models\Guideline;

class GuidelineController extends Controller
{
    protected GuidelineServiceInterface $guidelineService;

    public function __construct(GuidelineServiceInterface $guidelineService)
    {
        $this->guidelineService = $guidelineService;
    }

    public function list(Request $request, Message $message)
    {
        $type = $request->query('type', null);
        $isActive = $request->query('isActive', null);
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxGuidelinesPerPage = $request->query('maxContentPerPage', null);

        $filter = [
            'type' => $type,
            'isActive' => $isActive,
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_guideline_per_page' => $maxGuidelinesPerPage,
        ];

        $guidelines = $this->guidelineService->listGuidelines($filter);

        $message->setContent(200, 'Guidelines retrieved', '', $guidelines->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $guideline = $this->guidelineService->retrieveGuideline($id);

        $message->setContent(200, 'Guideline retrieved', '', [
            'guideline' => $guideline
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $type = $request->input('type');
        $content = $request->input('content');

        $guideline = $this->guidelineService->createGuideline($type, $content);

        if ($guideline instanceof Guideline) {
            $message->setContent(201, 'Guideline created', '', [
                'guideline' => $guideline
            ]);
        } else {
            $message->setContent(400, 'Guideline not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $type = $request->input('type');
        $content = $request->input('content');

        $isSuccess = $this->guidelineService->updateGuideline($id, $type, $content);

        if ($isSuccess) {
            $message->setContent(200, 'Guideline updated');
        } else {
            $message->setContent(400, 'Guideline not updated');
        }

        return $message->render();
    }

    public function setActive(Message $message, int $id)
    {

        $isSuccess = $this->guidelineService->setActive($id);

        if ($isSuccess) {
            $message->setContent(200, 'Guideline updated');
        } else {
            $message->setContent(400, 'Guideline not updated');
        }

        return $message->render();
    }

    public function deactivate(Message $message, int $id)
    {

        $isSuccess = $this->guidelineService->deactivate($id);

        if ($isSuccess) {
            $message->setContent(200, 'Guideline updated');
        } else {
            $message->setContent(400, 'Guideline not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $guideline = $this->guidelineService->retrieveGuideline($id);

        $isSuccess = $this->guidelineService->deleteGuideline($user, $guideline);

        if ($isSuccess) {
            $message->setContent(200, 'Guideline deleted');
        } else {
            $message->setContent(400, 'Guideline not updated');
        }

        return $message->render();
    }
}


