<?php

namespace App\Modules\Variants;

use App\Models\Variant as VariantModel;
use App\Modules\Item\Variant;
use App\Modules\Variants\Exceptions\InvalidColorHexException;
use App\Repository\VariantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VariantService implements VariantServiceInterface
{

    /**
     * Variant Repository
     * 
     * @var VariantRepositoryInterface $variantRepository
     */
    protected VariantRepositoryInterface $variantRepository;

    public function __construct(VariantRepositoryInterface $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    public function list(array $userFilters = []): Collection
    {
        return $this->variantRepository->list($userFilters);
    }

    public function createVariant(string $name): VariantModel
    {
        return $this->variantRepository->createVariant($name);
    }

    public function createElements(int $variantId, string $name, ?string $thumbnailType, $thumbnail, ?int $order = null): VariantModel
    {
        if (!is_null($thumbnailType) && $thumbnailType === Variant::THUMBNAIL_TYPE_COLOR) {
            if (is_null($thumbnail)) {
                throw new InvalidColorHexException('No color hex provided');
            }

            if ($this->isValidColorHex($thumbnail)) {
                $thumbnail = strtolower($thumbnail);
            } else {
                throw new InvalidColorHexException('Invalid hex provided.');
            }
        }

        return $this->variantRepository->createElements($variantId, $name, $thumbnailType, $thumbnail, $order);
    }

    public function updateVariant(int $id, string $name): bool
    {
        return $this->variantRepository->updateVariant($id, $name);
    }

    public function updateElements(int $elementId, string $name, ?string $thumbnailType, $thumbnail, ?int $order = null): bool
    {
        if (!is_null($thumbnailType) && $thumbnailType === Variant::THUMBNAIL_TYPE_COLOR) {
            if (is_null($thumbnail)) {
                throw new InvalidColorHexException('No color hex provided');
            }

            if ($this->isValidColorHex($thumbnail)) {
                $thumbnail = strtolower($thumbnail);
            } else {
                throw new InvalidColorHexException('Invalid hex provided.');
            }
        }

        return $this->variantRepository->updateElements($elementId, $name, $thumbnailType, $thumbnail, $order);
    }

    public function deleteVariant(int $variantId): bool
    {
        return $this->variantRepository->deleteVariant($variantId);
    }

    public function deleteElements(int $elementId): bool
    {
        return $this->variantRepository->deleteElements($elementId);
    }

    protected function isValidColorHex(string $hex): bool
    {
        $colorHexPattern = '/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/';
        return preg_match($colorHexPattern, $hex);
    }
}