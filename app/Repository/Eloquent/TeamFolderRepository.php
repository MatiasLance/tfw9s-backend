<?php

namespace App\Repository\Eloquent;

use App\Models\TeamFolder;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\TeamFolderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TeamFolderRepository extends BaseRepository implements teamFolderRepositoryInterface
{

    public function __construct(TeamFolder $team)
    {
        parent::__construct($team);
    }

    public function retrieveTeamFolder(int $id): TeamFolder
    {
        return TeamFolder::find($id);
    }

    public function updateTeamFolder(int $id, string $title, string $content): bool
    {
        $teamFolder = $this->find($id);
        $teamFolder->title = $title;
        $teamFolder->content = $content;

        return DB::transaction(function() use($teamFolder) {

            return $teamFolder->save();
        });
    }

}
