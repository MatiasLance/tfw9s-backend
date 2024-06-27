<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Faq\FaqServiceInterface;
use App\Modules\Http\Message;
use App\Models\Faq;

class FaqController extends Controller
{
    protected $faqService;

    public function __construct(FaqServiceInterface $faqService)
    {
        $this->faqService = $faqService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxFaqPerPage = $request->query('maxFaqPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_faq_per_page' => $maxFaqPerPage,
        ];

        $faq = $this->faqService->listFaq($filter);

        $message->setContent(200, 'Faq retrieved', '', $faq->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $faq = $this->faqService->retrieveFaq($id);

        $message->setContent(200, 'Faq retrieved', '', [
            'faq' => $faq
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $title = $request->input('title');
        $description = $request->input('description');
    
        $faq = $this->faqService->store($title, $description);

        if ($faq instanceof Faq) {
            $message->setContent(201, 'Faq created', '', [
                'faq' => $faq
            ]);
        } else {
            $message->setContent(400, 'Faq not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $title = $request->input('title');
        $description = $request->input('description');

        $isSuccess = $this->faqService->updateFaq($id, $title, $description);

        if ($isSuccess) {
            $message->setContent(200, 'Faq updated');
        } else {
            $message->setContent(400, 'Faq not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {
        $faq = $this->faqService->retrieveFaq($id);

        $isSuccess = $this->faqService->deleteFaq($faq);

        if ($isSuccess) {
            $message->setContent(200, 'Faq deleted');
        } else {
            $message->setContent(400, 'Faq not updated');
        }

        return $message->render();
    }
}
