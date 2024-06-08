<?php

namespace App\Modules\TeamFolder;

use App\Models\TeamFolder;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface TeamFolderServiceInterface
{

    /**
     * Retrieve an TeamFolder
     *
     * @param int $id
     *
     * @return TeamFolder
     */
    public function retrieveTeamFolder(int $id): TeamFolder;

    /**
     * Update an existing TeamFolder
     *
     * @param int $id
     * @param string $title
     * @param string $content
     *
     * @return bool
     */
    public function updateTeamFolder(int $id, string $title, string $content): bool;

}
