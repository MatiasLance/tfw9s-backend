<?php

namespace App\Repository;

use App\Models\Shipping;
use App\Models\NewShipping;
use Illuminate\Database\Eloquent\Collection;

interface ShippingRepositoryInterface
{

    public function retrieve(): NewShipping;

    public function create(
        string $name,
        string $country,
        float $shipping_value,
        float $insurance_value,
        float $registered_value,
        float $express_value
    ): bool;
    
    public function update(
        int $id,
        string $name,
        string $country,
        float $shipping_value,
        float $insurance_value,
        float $registered_value,
        float $express_value
    ): bool;

    public function delete(int $id): bool;
}