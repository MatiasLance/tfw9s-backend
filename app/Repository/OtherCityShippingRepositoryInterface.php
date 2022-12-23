<?php

namespace App\Repository;

use App\Models\OtherCityShipping;
use Illuminate\Database\Eloquent\Collection;

interface OtherCityShippingRepositoryInterface
{

    public function retrieve(): OtherCityShipping;

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