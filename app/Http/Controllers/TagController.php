<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Tags\TagServiceInterface;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Tag service
     * 
     * @var TagServiceInterface $tagService
     */
    protected TagServiceInterface $tagService;

    public function __construct(TagServiceInterface $tagService)
    {
        $this->tagService = $tagService;
    }

    public function list(Request $request, Message $message)
    {
        $tags = $this->tagService->listTags();

        $message->setContent(200, 'Tags retrieved', '', [
            'tags' => $tags
        ]);

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        //
    }

    public function delete(Request $request, Message $message, int $id)
    {
        //
    }
}
