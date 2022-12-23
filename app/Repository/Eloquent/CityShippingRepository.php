<?php

namespace App\Repository\Eloquent;

use App\Models\CityShipping;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\CityShippingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CityShippingRepository extends BaseRepository implements CityShippingRepositoryInterface
{


    public function __construct(CityShipping $shipping)
    {
        parent::__construct($shipping);
    }

    public function retrieve(): CityShipping
    {
        return CityShipping::latest()->first();
    }

    public function create(string $name, string $city, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool
    {
        $shipping = new CityShipping();
        $shipping->name = $name;
        $shipping->city = $city;
        $shipping->shipping_value = $shipping_value;
        $shipping->insurance_value = $insurance_value;
        $shipping->registered_value = $registered_value;
        $shipping->express_value = $express_value;

        return $shipping->save();
    }

    public function update(int $id, string $name, string $city, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool
    {
        $shipping = $this->find($id);
        $shipping->name = $name;
        $shipping->city = $city;
        $shipping->shipping_value = $shipping_value;
        $shipping->insurance_value = $insurance_value;
        $shipping->registered_value = $registered_value;
        $shipping->express_value = $express_value;

        return $shipping->save();
    }

    public function delete(int $id): bool
    {
        $shipping = $this->find($id);

        return $shipping->delete();
    }
}