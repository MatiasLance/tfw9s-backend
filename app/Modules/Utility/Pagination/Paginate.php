<?php

namespace App\Modules\Utility\Pagination;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Paginate
{
    /**
     * The builder instance of the model to paginate
     * 
     * @var Builder $builder
     */
    protected Builder $builder;

    /**
     * Number of items to be in a page
     * 
     * @var int $itemsPerPage
     */
    protected int $itemsPerPage = 15;

    /**
     * Current page to generate
     * 
     * @var int $currentPage
     */
    protected int $currentPage = 1;

    /**
     * String to label the array of paginated data.
     * 
     * @var string $label
     */
    protected string $label = 'data';

    /**
     * Attributes to append to the paginated items.
     * 
     * @var array $appends
     */
    protected array $appends = [];

    /** @var null|callable */
    protected $itemTransformer = null;

    /**
     * @param Builder|Model $builder The data to paginate
     * @param null|int $itemsPerPage
     * @param null|int $page
     * @param null|string $label
     * 
     * @return void
     */
    public function __construct($builder, ?int $itemsPerPage = null, ?int $page = 1, ?string $label = null, ?array $appends = [])
    {
        if ($builder instanceof Builder) {
            $this->builder = $builder;
        } else if ($builder instanceof Model) {
            $this->builder = $builder->query();
        }

        if (!is_null($itemsPerPage)) {
            $this->itemsPerPage = $itemsPerPage;
        }

        if (!is_null($page) && $page > 0) {
            $this->currentPage = $page;
        }

        if (!is_null($label)) {
            $this->label = $label;
        }

        if (!is_null($appends)) {
            $this->appends = $appends;
        }
    }

    /**
     * Get the total number of pages
     * 
     * @param int $totalItems
     * 
     * @return int
     */
    public function getNumberOfPages(int $totalItems): int
    {
        if ($totalItems > $this->itemsPerPage) {
            $lastPageItems = $totalItems % $this->itemsPerPage;
            $totalPages = floor($totalItems / $this->itemsPerPage);
            if ($lastPageItems > 0) {
                $totalPages++;
            }
        } elseif ($totalItems === 0) {
            $totalPages = 0;
        } else {
            $totalPages = 1;
        }

        return $totalPages;
    }

    /**
     * Retrieve the range of the items shown in the pagination
     * 
     * @param int $totalItems
     * 
     * @return array
     */
    public function getItemRange(int $totalItems): array
    {
        $from = (($this->currentPage - 1) * $this->itemsPerPage) + 1;
        $to = ($this->currentPage * $this->itemsPerPage);
        if ($to > $totalItems) {
            $to = $totalItems;
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * Generate the paginated data
     * 
     * @return array
     */
    public function toArray(): array
    {
        $totalItems = $this->builder->count();
        $itemsToSkip = ($this->currentPage - 1) * $this->itemsPerPage;
        $numberOfPages = $this->getNumberOfPages($totalItems);
        $itemRange = $this->getItemRange($totalItems);
        $appends = $this->appends;
        $items = $this->builder
                            ->skip($itemsToSkip)
                            ->take($this->itemsPerPage)
                            ->get();

        if ($this->itemTransformer) {
            $items->transform($this->itemTransformer);
        }

        $items->append($appends);
        return [
            'current_page' => $this->currentPage,
            'last_page' => $numberOfPages,
            'items_per_page' => $this->itemsPerPage,
            'page_length' => count($items),
            'total_items' => $totalItems,
            'from' => $itemRange['from'],
            'to' => $itemRange['to'],
            $this->label => $items->toArray()
        ];
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }

    public function transformItems(callable $transformer): self
    {
        $this->itemTransformer = $transformer;

        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
