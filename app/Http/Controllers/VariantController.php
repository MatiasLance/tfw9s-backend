<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Variant\VariantServiceInterface;
use App\Modules\Http\Message;

class VariantController extends Controller
{
    protected $variantService;

    public function __construct(VariantServiceInterface $variantService)
    {
        $this->variantService = $variantService;
    }

    public function retrieve(Message $message)
    {
        $variant = $this->variantService->retrieveVariant();

        $message->setContent(200, 'Variant retrieved', '', [
            'variant' => $variant
        ]);

        return $message->render();

    }

    public function store(Request $request, Message $message)
    {
        $itemId = $request->input('item_id');
        $color = $request->input('color') ?? [];

        $isSuccess = $this->variantService->addVariant($itemId, $color,);

        if ($isSuccess) {
            $message->setContent(200, 'Item variant added: Item ID ' . $itemId . ', Color ' . implode(', ', $color));
        } else {
            $message->setContent(400, 'Item variant not added');
        }
        return $message->render();
    }

    public function itemvariant(int $id, Message $message)
    {
        $variant = $this->variantService->retrieveItemVariant($id);

        if ($variant) {
            $message->setContent(200, 'Variant retrieved', '', [
                'variant' => $variant
            ]);
        }

        return $message->render();
    }

    public function delete(int $variantId, Message $message)
    {
        $isDeleted = $this->variantService->deleteVariant($variantId);

        if ($isDeleted) {
            $message->setContent(200, 'Variant deleted: ID ' . $variantId);
        } else {
            $message->setContent(404, 'Variant not found or could not be deleted');
        }

        return $message->render();
    }
}