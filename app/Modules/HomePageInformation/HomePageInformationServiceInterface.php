<?php

namespace App\Modules\HomePageInformation;

use App\Models\HomePageInformation;

interface HomePageInformationServiceInterface
{

    /**
     * Retrieve an HomePageInformation
     *
     * @param int $id
     *
     * @return HomePageInformation
     */
    public function retrieveHomePageInfo(int $id): HomePageInformation;

    /**
     * Update an existing HomePageInformation
     *
     * @param int $id
     * @param string $description
     * @param array $media
     *
     * @return bool
     */
    public function updateHomePageInfo(int $id, string $content, ?array $media): bool;


}

