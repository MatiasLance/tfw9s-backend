<?php

namespace App\Repository;

use App\Models\CityShipping;
use Illuminate\Database\Eloquent\Collection;

interface CityShippingRepositoryInterface
{

    public function retrieve(): CityShipping;

    public function create(
        string $name,
        string $city,
        float $shipping_value,
        float $insurance_value,
        float $registered_value,
        float $express_value
    ): bool;
    
    public function update(
        int $id,
        string $name,
        string $city,
        float $shipping_value,
        float $insurance_value,
        float $registered_value,
        float $express_value
    ): bool;

    public function delete(int $id): bool;
}