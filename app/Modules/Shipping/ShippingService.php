<?php

namespace App\Modules\Shipping;

use App\Models\Shipping;
use App\Models\NewShipping;
use App\Repository\ShippingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ShippingService implements ShippingServiceInterface
{
    protected ShippingRepositoryInterface $shippingRepository;

    public function __construct(ShippingRepositoryInterface $shippingRepository)
    {
        $this->shippingRepository = $shippingRepository;
    }
 
    public function retrieve(): NewShipping {
        return $this->shippingRepository->retrieve();
    }

    public function create(string $name, string $country, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool {
        return $this->shippingRepository->create($name, $country, $shipping_value, $insurance_value, $registered_value, $express_value);
    }

    public function update(int $id, string $name, string $country, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool {
        return $this->shippingRepository->update($id, $name, $country, $shipping_value, $insurance_value, $registered_value, $express_value);
    }

    public function delete(int $id): bool {
        return $this->shippingRepository->delete($id);
    }
}