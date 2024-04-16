<?php

namespace App\Repository;

use App\Models\PartnerSponsor;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface PartnerSponsorRepositoryInterface
{
    /**
     * Maximum partnerSponsors to be shown per page
     *
     * @var int MAX_PAGE_PARTNERSPONSORS
     */
    public const MAX_PAGE_PARTNERSPONSORS = 12;

    /**
     * Placeholder partnerSponsor name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_partnerSponsor_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of partnerSponsors.
     *
     * @param array $userFilters
     *
     * @return Paginate<partnerSponsor>
     */
    public function listPartnerSponsors(array $userFilters = []): Paginate;

    /**
     * Retrieve an partnerSponsor
     *
     * @param int $id
     *
     * @return partnerSponsor
     */
    public function retrievePartnerSponsor(int $id): PartnerSponsor;

    /**
     * Create a new partnerSponsor instance
     *
     * @param string $company_name
     * @param string $first_name
     * @param string $last_name
     * @param string $description
     * @param ?array $media
     *
     * @return partnerSponsor
     */
    public function createPartnerSponsor(string $company_name, string $first_name, string $last_name, string $description, ?array $media): PartnerSponsor;

    /**
     * Update an existing partnerSponsor instance
     *
     * @param int $id
     * @param string $company_name
     * @param string $first_name
     * @param string $last_name
     * @param string $description
     * @param ?array $media
     *
     * @return bool
     */
    public function updatePartnerSponsor(int $id, string $company_name, string $first_name, string $last_name, string $description, ?array $media): bool;

    /**
     * Delete an existing partnerSponsor instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deletePartnerSponsor(int $id): bool;

}
