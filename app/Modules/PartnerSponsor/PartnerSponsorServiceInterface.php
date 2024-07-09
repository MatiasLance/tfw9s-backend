<?php

namespace App\Modules\PartnerSponsor;

use App\Models\PartnerSponsor;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface PartnerSponsorServiceInterface
{
    /**
     * Retrieve a list of partnerSponsors
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<PartnerSponsor>
     */
    public function listPartnerSponsors(array $filters = []): Paginate;

    /**
     * Retrieve an PartnerSponsor
     *
     * @param int $id
     *
     * @return PartnerSponsor
     */
    public function retrievePartnerSponsor(int $id): PartnerSponsor;

    /**
     * Create a new PartnerSponsor
     *
     * @param string $company_name
     * @param string $hyperlink
     * @param string $first_name
     * @param string $last_name
     * @param string $description
     * @param ?array $media
     *
     * @return PartnerSponsor
     */
    public function createPartnerSponsor(string $company_name, string $hyperlink, string $first_name, string $last_name, string $description, ?array $media): PartnerSponsor;

    /**
     * Update an existing PartnerSponsor
     *
     * @param int $id
     * @param string $company_name
     * @param string $hyperlink
     * @param string $first_name
     * @param string $last_name
     * @param string $description
     * @param ?array $media
     *
     * @return bool
     */
    public function updatePartnerSponsor(int $id, string $company_name, string $hyperlink, string $first_name, string $last_name, string $description, ?array $media): bool;

    /**
     * Delete an existing PartnerSponsor
     *
     * @param User $initiator The user who initiated the delete command
     * @param PartnerSponsor $partnerSponsor The partnerSponsor to be deleted
     *
     * @return bool
     */
    public function deletePartnerSponsor(User $initiator, PartnerSponsor $partnerSponsor): bool;

    public function countPartnerSponsor();

}
