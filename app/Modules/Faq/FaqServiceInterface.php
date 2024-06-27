<?php

namespace App\Modules\Faq;

use App\Models\Faq;
use App\Modules\Utility\Pagination\Paginate;

interface FaqServiceInterface
{
     /**
     * Retrieve a list of faq
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Faq>
     */
    public function listFaq(array $filters = []): Paginate;

    /**
     * Retrieve an News
     *
     * @param int $id
     *
     * @return Faq
     */
    public function retrieveFaq(int $id): Faq;

    public function store(string $title, string $description): Faq;

    /**
     * Update an existing Faq
     *
     * @param string $title
     * @param string $description
     */
    public function updateFaq(int $id, string $title, string $description): bool;

    /**
     * Delete an existing Faq
     *
     * @param User $initiator The user who initiated the delete command
     * @param Faq $faq The faq to be deleted
     *
     * @return bool
     */
    public function deleteFaq(Faq $faq): bool;

}
