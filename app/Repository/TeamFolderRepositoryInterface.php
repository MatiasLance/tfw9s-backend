<?php

namespace App\Repository;

use App\Models\TeamFolder;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface TeamFolderRepositoryInterface
{

    /**
     * Retrieve an teamFolder
     *
     * @param int $id
     *
     * @return teamFolder
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
