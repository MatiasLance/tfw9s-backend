<?php

namespace App\Modules\Discount;

use App\Models\DiscountCode;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface DiscountServiceInterface
{
    /**
     * Retrieve a list of discount
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<DiscountCode>
     */
    public function listDiscount(array $filters = []): Paginate;

}