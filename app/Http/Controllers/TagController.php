<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Item\ItemServiceInterface;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Item service
     * 
     * @var ItemServiceInterface $itemService
     */
    protected ItemServiceInterface $itemService;

    public function __construct(ItemServiceInterface $itemService)
    {
        $this->itemService = $itemService;
    }

    public function list(Request $request, Message $message)
    {
        $tags = $this->itemService->listTags();

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
