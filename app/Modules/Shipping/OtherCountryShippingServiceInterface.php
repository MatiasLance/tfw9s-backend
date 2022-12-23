<?php

namespace App\Modules\Shipping;

use App\Models\OtherCountryShipping;
use Illuminate\Database\Eloquent\Collection;

interface OtherCountryShippingServiceInterface
{

    public function retrieve(): OtherCountryShipping;

    public function create(string $name, string $country, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool;

    public function update(int $id, string $name, string $country, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool;

    public function delete(int $id): bool;
}