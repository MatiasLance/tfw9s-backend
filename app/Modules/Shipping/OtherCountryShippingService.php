<?php

namespace App\Modules\Shipping;

use App\Models\OtherCountryShipping;
use App\Repository\OtherCountryShippingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OtherCountryShippingService implements OtherCountryShippingServiceInterface
{
    protected OtherCountryShippingRepositoryInterface $shippingRepository;

    public function __construct(OtherCountryShippingRepositoryInterface $shippingRepository)
    {
        $this->shippingRepository = $shippingRepository;
    }
 
    public function retrieve(): OtherCountryShipping {
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