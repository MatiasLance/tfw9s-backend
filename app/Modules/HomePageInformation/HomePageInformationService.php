<?php

namespace App\Modules\HomePageInformation;

use App\Models\HomePageInformation;
use App\Repository\HomePageInformationRepositoryInterface;

class HomePageInformationService implements HomePageInformationServiceInterface
{
    /**
     * HomePageInformationRepository
     *
     * @var HomePageInformationRepositoryInterface $homePageInformationRepository
     */
    protected HomePageInformationRepositoryInterface $homePageInformationRepository;

    public function __construct(HomePageInformationRepositoryInterface $homePageInformationRepository)
    {
        $this->homePageInformationRepository = $homePageInformationRepository;
    }

    public function retrieveHomePageInfo(int $id): HomePageInformation
    {
        return $this->homePageInformationRepository->retrieveHomePageInfo($id);
    }

    public function updateHomePageInfo(int $id, string $content, ?array $media): bool
    {
        return $this->homePageInformationRepository->updateHomePageInfo($id, $content, $media);
    }

}
