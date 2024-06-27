<?php

namespace App\Repository;

use App\Models\Faq;
use App\Modules\Utility\Pagination\Paginate;

interface FaqRepositoryInterface
{
    /**
     * Maximum faq to be shown per page
     *
     * @var int MAX_PAGE_FAQ
     */
    public const MAX_PAGE_FAQ = 12;

    /**
     * Placeholder faq name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_faq_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of faq.
     *
     * @param array $userFilters
     *
     * @return Paginate<faq>
     */
    public function listFaq(array $userFilters = []): Paginate;

    /**
     * Retrieve an faq
     *
     * @param int $id
     *
     * @return faq
     */
    public function retrieveFaq(int $id): Faq;

    public function store(string $title, string $description): Faq;

    /**
     * Update an existing faq instance
     *
     * @param int $id
     * @param string $title
     * @param string $description
     *
     * @return bool
     */
    public function updateFaq(int $id, string $title, string $description): bool;

    /**
     * Delete an existing news instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteFaq(int $id): bool;
}
