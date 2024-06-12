<?php

namespace App\Repository;

use App\Models\Variant;

interface VariantRepositoryInterface
{
    public function retrieveVariant(): ?array;

    public function addVariant($itemId, $colors);

    public function retrieveItemVariant(int $id): ?array;

    public function deleteVariant(int $variantId): bool;
}
