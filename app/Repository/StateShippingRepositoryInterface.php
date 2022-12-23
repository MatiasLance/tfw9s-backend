<?php

namespace App\Repository;

use App\Models\StateShipping;
use Illuminate\Database\Eloquent\Collection;

interface StateShippingRepositoryInterface
{

    public function retrieve(): StateShipping;

    public function create(
        string $name,
        string $state,
        float $shipping_value,
        float $insurance_value,
        float $registered_value,
        float $express_value
    ): bool;
    
    public function update(
        int $id,
        string $name,
        string $state,
        float $shipping_value,
        float $insurance_value,
        float $registered_value,
        float $express_value
    ): bool;

    public function delete(int $id): bool;
}