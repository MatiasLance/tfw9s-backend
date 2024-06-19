<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Variant\VariantServiceInterface;
use App\Modules\Http\Message;
use App\Models\Variant;

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
        $colors = $request->input('color') ?? [];
    
        $result = $this->variantService->addVariant($itemId, $colors);
    
        if ($result['status'] == 'success') {
            $message->setContent(200, 'success');
        } elseif ($result['status'] == 'exists') {
            $message->setContent(200, 'exists');
        } else {
            $message->setContent(400, 'Item variant not added');
        }
        return $message->render();
    }    

    public function storeVariant(Request $request, Message $message)
    {
        $name = $request->input('name');
        $variant = $this->variantService->storeVariant($name);

        if ($variant instanceof Variant) {
            $message->setContent(201, 'Variant created', '', [
                'variant' => $variant
            ]);
        } else {
            $message->setContent(400, 'Region not created');
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