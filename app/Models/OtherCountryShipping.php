<?php

namespace App\Models;

use App\Modules\Currency\Traits\HandlesCurrency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherCountryShipping extends Model
{
    use HasFactory;
    use HandlesCurrency;

    protected $fillable = [
        'name',
        'country',
        'shipping_value',
        'insurance_value',
        'registered_value',
        'express_value'
    ];

    public function shippingValue(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function insuranceValue(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function registeredValue(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function expressValue(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function shippingCentPrice(): int
    {
        return $this->getAttributes()['shipping_value'];
    }

    public function insuranceCentPrice(): int
    {
        return $this->getAttributes()['insurance_value'];
    }

    public function registeredCentPrice(): int
    {
        return $this->getAttributes()['registered_value'];
    }

    public function expressCentPrice(): int
    {
        return $this->getAttributes()['express_value'];
    }
}
