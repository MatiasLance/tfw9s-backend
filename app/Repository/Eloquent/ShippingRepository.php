<?php

namespace App\Repository\Eloquent;

use App\Models\NewShipping;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\ShippingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ShippingRepository extends BaseRepository implements ShippingRepositoryInterface
{


    public function __construct(NewShipping $shipping)
    {
        parent::__construct($shipping);
    }

    public function retrieve(): NewShipping
    {
        return NewShipping::latest()->first();
    }

    public function create(string $name, string $country, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool
    {
        $shipping = new NewShipping();
        $shipping->name = $name;
        $shipping->country = $country;
        $shipping->shipping_value = $shipping_value;
        $shipping->insurance_value = $insurance_value;
        $shipping->registered_value = $registered_value;
        $shipping->express_value = $express_value;

        return $shipping->save();
    }

    public function update(int $id, string $name, string $country, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool
    {
        $shipping = $this->find($id);
        $shipping->name = $name;
        $shipping->country = $country;
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