<?php

namespace App\Modules\Shipping;

use App\Models\CityShipping;
use App\Repository\CityShippingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CityShippingService implements CityShippingServiceInterface
{
    protected CityShippingRepositoryInterface $shippingRepository;

    public function __construct(CityShippingRepositoryInterface $shippingRepository)
    {
        $this->shippingRepository = $shippingRepository;
    }
 
    public function retrieve(): CityShipping {
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