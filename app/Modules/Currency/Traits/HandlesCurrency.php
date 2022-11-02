<?php

namespace App\Modules\Currency\Traits;

trait HandlesCurrency
{
    /**
     * Value of cents. This will be used to convert the price to
     * cents. This is to avoid float rounding errors.
     * 
     * @var int $centValue
     */
    private $centValue = 100;

    /**
     * Converts the price to its cent value
     * 
     * @param float|int $price
     * @return int
     */
    public function toCent($price)
    {
        return intval(strval($price * $this->centValue));
    }

    /**
     * Converts the price to its basic unit.
     * 
     * @param int $price
     * @return float
     */
    public function toPrice(int $price)
    {
        return floatval(strval($price / $this->centValue));
    }

    /**
     * Returns the centValue
     * 
     * @return int
     */
    public function getCentValue()
    {
        return $this->centValue;
    }
}