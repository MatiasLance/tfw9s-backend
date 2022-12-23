<?php

namespace App\Modules\Shipping;

use App\Models\OtherCityShipping;
use App\Repository\OtherCityShippingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OtherCityShippingService implements OtherCityShippingServiceInterface
{
    protected OtherCityShippingRepositoryInterface $shippingRepository;

    public function __construct(OtherCityShippingRepositoryInterface $shippingRepository)
    {
        $this->shippingRepository = $shippingRepository;
    }
 
    public function retrieve(): OtherCityShipping {
        return $this->shippingRepository->retrieve();
    }

    public function create(string $name, string $city, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool {
        return $this->shippingRepository->create($name, $city, $shipping_value, $insurance_value, $registered_value, $express_value);
    }

    public function update(int $id, string $name, string $city, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool {
        return $this->shippingRepository->update($id, $name, $city, $shipping_value, $insurance_value, $registered_value, $express_value);
    }

    public function delete(int $id): bool {
        return $this->shippingRepository->delete($id);
    }
}