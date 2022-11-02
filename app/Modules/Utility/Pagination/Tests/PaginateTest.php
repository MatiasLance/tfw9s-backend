<?php

namespace App\Modules\Utility\Pagination\Tests;

use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Tests\TestCase;

class PaginateTest extends TestCase
{
    /**
     * Test retrieving number of total pages
     * 
     * @dataProvider get_number_of_pages_provider
     * 
     * @param int $totalItems
     * @param int $itemsPerPage
     * @param int $totalPages
     * 
     * @return void
     */
    public function test_get_number_of_pages(int $totalItems, int $itemsPerPage, int $expectedTotalPages)
    {
        $paginator = new Paginate(User::query(), $itemsPerPage);

        $totalPages = $paginator->getNumberOfPages($totalItems);

        $this->assertEquals($expectedTotalPages, $totalPages);
    }

    /**
     * Provider for the test_get_number_of_pages() test
     * 
     * @return array
     */
    public function get_number_of_pages_provider(): array
    {
        return [
            "Exact items on page" => [8, 8, 1],
            "Even distribution of items" => [4, 2, 2],
            "With remainder of items" => [50, 20, 3],
            "Total items is less than items per page" => [8, 16, 1],
            "Zero total items" => [0, 10, 0]
        ];
    }
}