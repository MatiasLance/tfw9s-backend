<?php

namespace App\Http\Controllers;


use App\Modules\Http\Message;
use App\Modules\News\NewsServiceInterface;
use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    protected NewsServiceInterface $newsService;

    public function __construct(NewsServiceInterface $newsService)
    {
        $this->newsService = $newsService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxNewsPerPage = $request->query('maxNewsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_news_per_page' => $maxNewsPerPage,
        ];

        $news = $this->newsService->listNews($filter);

        $message->setContent(200, 'News retrieved', '', $news->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $news = $this->newsService->retrieveNews($id);

        $message->setContent(200, 'News retrieved', '', [
            'news' => $news
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $headline = $request->input('headline');
        $lead = $request->input('lead') ?? '';
        $body = $request->input('body');

        $news = $this->newsService->createNews($headline, $lead, $body);

        if ($news instanceof News) {
            $message->setContent(201, 'News created', '', [
                'news' => $news
            ]);
        } else {
            $message->setContent(400, 'News not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $headline = $request->input('headline');
        $lead = $request->input('lead') ?? '';
        $body = $request->input('body');

        $isSuccess = $this->newsService->updateNews($id, $headline, $lead, $body);

        if ($isSuccess) {
            $message->setContent(200, 'News updated');
        } else {
            $message->setContent(400, 'News not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $news = $this->newsService->retrieveNews($id);

        $isSuccess = $this->newsService->deleteNews($user, $news);

        if ($isSuccess) {
            $message->setContent(200, 'News deleted');
        } else {
            $message->setContent(400, 'News not updated');
        }

        return $message->render();
    }
}
