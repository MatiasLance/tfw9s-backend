<?php

namespace App\Modules\Shipping;

use App\Models\OtherStateShipping;
use App\Repository\OtherStateShippingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OtherStateShippingService implements OtherStateShippingServiceInterface
{
    protected OtherStateShippingRepositoryInterface $shippingRepository;

    public function __construct(OtherStateShippingRepositoryInterface $shippingRepository)
    {
        $this->shippingRepository = $shippingRepository;
    }
 
    public function retrieve(): OtherStateShipping {
        return $this->shippingRepository->retrieve();
    }

    public function create(string $name, string $state, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool {
        return $this->shippingRepository->create($name, $state, $shipping_value, $insurance_value, $registered_value, $express_value);
    }

    public function update(int $id, string $name, string $state, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool {
        return $this->shippingRepository->update($id, $name, $state, $shipping_value, $insurance_value, $registered_value, $express_value);
    }

    public function delete(int $id): bool {
        return $this->shippingRepository->delete($id);
    }
}