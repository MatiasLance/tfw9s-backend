<?php

namespace App\Repository;

use App\Models\HomePageInformation;

interface HomePageInformationRepositoryInterface
{

    /**
     * Placeholder HomePageInformation name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_series_placeholder_thumbnail.jpg';

    /**
     * Retrieve an HomePageInformation
     *
     * @param int $id
     *
     * @return HomePageInformation
     */
    public function retrieveHomePageInfo(int $id): HomePageInformation;

    /**
     * Update an existing series instance
     *
     * @param int $id
     * @param string $content
     * @param array $media
     *
     * @return bool
     */
    public function updateHomePageInfo(int $id, string $content, ?array $media): bool;

}
