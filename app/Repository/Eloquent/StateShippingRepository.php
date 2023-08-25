<?php

namespace App\Repository\Eloquent;

use App\Models\StateShipping;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\StateShippingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StateShippingRepository extends BaseRepository implements StateShippingRepositoryInterface
{


    public function __construct(StateShipping $shipping)
    {
        parent::__construct($shipping);
    }

    public function retrieve(): StateShipping
    {
        return StateShipping::latest()->first();
    }

    public function create(string $name, string $state, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool
    {
        $shipping = new StateShipping();
        $shipping->name = $name;
        $shipping->state = $state;
        $shipping->shipping_value = $shipping_value;
        $shipping->insurance_value = $insurance_value;
        $shipping->registered_value = $registered_value;
        $shipping->express_value = $express_value;

        return $shipping->save();
    }

    public function update(int $id, string $name, string $state, float $shipping_value, float $insurance_value, float $registered_value, float $express_value): bool
    {
        $shipping = $this->find($id);
        $shipping->name = $name;
        $shipping->state = $state;
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