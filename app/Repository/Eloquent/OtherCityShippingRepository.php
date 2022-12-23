<?php

namespace App\Repository\Eloquent;

use App\Models\OtherCityShipping;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\OtherCityShippingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OtherCityShippingRepository extends BaseRepository implements OtherCityShippingRepositoryInterface
{


    public function __construct(OtherCityShipping $shipping)
    {
        parent::__construct($shipping);
    }

    public function retrieve(): OtherCityShipping
    {
        return OtherCityShipping::latest()->first();
    }

    public function create(string $name, string $city, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool
    {
        $shipping = new OtherCityShipping();
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