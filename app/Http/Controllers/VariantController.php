<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Item\Variant;
use App\Modules\Variants\VariantServiceInterface;
use Illuminate\Http\Request;

class VariantController extends Controller
{

    /**
     * Variant Service
     * 
     * @var VariantServiceInterface $variantService
     */
    protected VariantServiceInterface $variantService;

    public function __construct(VariantServiceInterface $variantService)
    {
        $this->variantService = $variantService;
    }

    public function list(Request $request, Message $message)
    {
        $user = $request->user();
        $q = $request->input('q');

        $filters = [
            'q' => $q
        ];

        $variants = $this->variantService->list($filters);

        $message->setContent(200, 'List of variatns retrieved', '', [
            'variants' => $variants
        ]);

        return $message->render();
    }

    public function retrieveVariant(Request $request, Message $message, int $variantId)
    {
        $user = $request->user();

        $variant = $this->variantService->retrieveVariant($variantId);

        $message->setContent(
            status: 200,
            title: 'Variant retrieved',
            data: [
                'variant' => $variant
            ],
        );

        return $message->render();
    }

    public function retrieveElement(Request $request, Message $message, int $elementId)
    {
        $user = $request->user();

        $element = $this->variantService->retrieveElement($elementId);

        $message->setContent(
            status: 200,
            title: 'Element retrieved',
            data: [
                'element' => $element
            ],
        );

        return $message->render();
    }


    public function storeVariant(Request $request, Message $message)
    {
        $user = $request->user();
        $name = $request->input('name');

        $variant = $this->variantService->createVariant($name);

        if (!is_null($variant)) {
            $message->setContent(201, 'Variant created', '', [
                'variant' => $variant
            ]);
        } else {
            $message->setContent(400, 'Cannot create variant');
        }

        return $message->render();
    }

    public function storeElements(Request $request, Message $message, int $variantId)
    {
        $user = $request->user();
        $name = $request->input('name');
        $thumbnailType = $request->input('thumbnail_type');

        if ($thumbnailType === Variant::THUMBNAIL_TYPE_IMAGE) {
            $thumbnail = $request->file('thumbnail');
        } else {
            $thumbnail = $request->input('thumbnail');
        }

        $order = $request->input('order');

        $variant = $this->variantService->createElements($variantId, $name, $thumbnailType, $thumbnail, $order);

        if (!is_null($variant)) {
            $message->setContent(201, 'Element created', '', [
                'variant' => $variant
            ]);
        } else {
            $message->setContent(400, 'Cannot create element');
        }

        return $message->render();
    }

    public function updateVariant(Request $request, Message $message, int $variantId)
    {
        $user = $request->user();
        $name = $request->input('name');
        
        $isSuccess = $this->variantService->updateVariant($variantId, $name);

        if ($isSuccess) {
            $message->setContent(200, 'Variant updated');
        } else {
            $message->setContent(400, 'Cannot update variant');
        }

        return $message->render();
    }

    public function updateElements(Request $request, Message $message, int $elementId)
    {
        $user = $request->user();
        $name = $request->input('name');
        $thumbnailType = $request->input('thumbnail_type') ?? null;

        if ($thumbnailType === Variant::THUMBNAIL_TYPE_IMAGE) {
            $thumbnail = $request->file('thumbnail');
        } else {
            $thumbnail = $request->input('thumbnail');
        }

        $order = $request->input('order');

        $isSuccess = $this->variantService->updateElements($elementId, $name, $thumbnailType, $thumbnail, $order);

        if ($isSuccess) {
            $message->setContent(200, 'Element updated');
        } else {
            $message->setContent(400, 'Cannot updated element');
        }

        return $message->render();
    }

    public function deleteVariant(Request $request, Message $message, int $variantId)
    {
        $user = $request->user();

        $isSuccess = $this->variantService->deleteVariant($variantId);

        if ($isSuccess) {
            $message->setContent(200, 'Variant deleted');
        } else {
            $message->setContent(400, 'Cannot delete variant');
        }

        return $message->render();
    }

    public function deleteElements(Request $request, Message $message, int $elementId)
    {
        $user = $request->user();

        $isSuccess = $this->variantService->deleteElements($elementId);

        if ($isSuccess) {
            $message->setContent(200, 'Element deleted');
        } else {
            $message->setContent(400, 'Cannot delete element');
        }

        return $message->render();
    }

}
