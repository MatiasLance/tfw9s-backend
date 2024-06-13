<?php

namespace App\Repository;

use App\Models\DiscountCode;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface DiscountRepositoryInterface
{
    /**
     * Maximum regions to be shown per page
     *
     * @var int MAX_PAGE_DISCOUNT
     */
    public const MAX_PAGE_DISCOUNT = 12;

    /**
     * Placeholder discount name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_discount_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of discount.
     *
     * @param array $userFilters
     *
     * @return Paginate<DiscountCode>
     */
    public function listDiscount(array $userFilters = []): Paginate;
}
