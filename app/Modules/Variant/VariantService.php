<?php

namespace App\Modules\Variant;

use App\Models\Variant;
use App\Repository\VariantRepositoryInterface;

class VariantService implements VariantServiceInterface
{
    protected VariantRepositoryInterface $variantRepository;

    public function __construct(VariantRepositoryInterface $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    public function retrieveVariant(): ?array
    {
        return $this->variantRepository->retrieveVariant();
    }

    public function addVariant($itemId, $colors)
    {
        return $this->variantRepository->addVariant($itemId, $colors);
    }

    public function retrieveItemVariant(int $id): ?array
    {
        return $this->variantRepository->retrieveItemVariant($id);
    }

    public function deleteVariant(int $variantId): bool
    {
        return $this->variantRepository->deleteVariant($variantId);
    }
}
