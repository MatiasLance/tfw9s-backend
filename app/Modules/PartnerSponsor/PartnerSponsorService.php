<?php

namespace App\Modules\PartnerSponsor;

use App\Models\User;
use App\Models\PartnerSponsor;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\PartnerSponsorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PartnerSponsorService implements PartnerSponsorServiceInterface
{
    /**
     * PartnerSponsor Repository
     *
     * @var PartnerSponsorRepositoryInterface $partnerSponsorRepository
     */
    protected PartnerSponsorRepositoryInterface $partnerSponsorRepository;

    public function __construct(PartnerSponsorRepositoryInterface $partnerSponsorRepository)
    {
        $this->partnerSponsorRepository = $partnerSponsorRepository;
    }

    public function listPartnerSponsors(array $filters = []): Paginate
    {
        return $this->partnerSponsorRepository->listPartnerSponsors($filters);
    }

    public function retrievePartnerSponsor(int $id): PartnerSponsor
    {
        return $this->partnerSponsorRepository->retrievePartnerSponsor($id);
    }

    public function createPartnerSponsor(string $company_name, string $first_name, string $last_name, string $description, ?array $media): PartnerSponsor
    {
        return $this->partnerSponsorRepository->createPartnerSponsor($company_name, $first_name, $last_name, $description, $media);
    }

    public function updatePartnerSponsor(int $id, string $company_name, string $first_name, string $last_name, string $description, ?array $media): bool
    {
        return $this->partnerSponsorRepository->updatePartnerSponsor($id, $company_name, $first_name, $last_name, $description, $media);
    }

    public function deletePartnerSponsor(User $initiator, PartnerSponsor $partnerSponsor): bool
    {
        return $this->partnerSponsorRepository->deletePartnerSponsor($partnerSponsor->id);
    }
}
