<?php

namespace App\Modules\Discount;

use App\Models\DiscountCode;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\DiscountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DiscountService implements DiscountServiceInterface
{
    /**
     * Discount Repository
     *
     * @var DiscountRepositoryInterface $discountRepository
     */
    protected DiscountRepositoryInterface $discountRepository;

    public function __construct(DiscountRepositoryInterface $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    public function listDiscount(array $filters = []): Paginate
    {
        return $this->discountRepository->listDiscount($filters);
    }

}
