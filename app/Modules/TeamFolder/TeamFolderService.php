<?php

namespace App\Modules\TeamFolder;

use App\Models\User;
use App\Models\TeamFolder;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\TeamFolderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TeamFolderService implements TeamFolderServiceInterface
{
    /**
     * TeamFolder Repository
     *
     * @var TeamFolderRepositoryInterface $teamFolderRepository
     */
    protected TeamFolderRepositoryInterface $teamFolderRepository;

    public function __construct(TeamFolderRepositoryInterface $teamFolderRepository)
    {
        $this->teamFolderRepository = $teamFolderRepository;
    }

    public function retrieveTeamFolder(int $id): TeamFolder
    {
        return $this->teamFolderRepository->retrieveTeamFolder($id);
    }

    public function updateTeamFolder(int $id, string $title, string $content): bool
    {
        return $this->teamFolderRepository->updateTeamFolder($id, $title, $content);
    }

}
