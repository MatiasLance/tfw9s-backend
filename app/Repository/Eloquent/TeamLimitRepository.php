<?php

namespace App\Repository\Eloquent;

use App\Models\AgeGroup;
use App\Models\TeamLimit;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\TeamLimitRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TeamLimitRepository extends BaseRepository implements TeamLimitRepositoryInterface
{

    public function __construct(TeamLimit $teamLimit)
    {
        parent::__construct($teamLimit);
    }

    public function listTeamLimits(int $series_id): array
    {
        $teamlimitQuery = $this->model->query();

        $teamlimits = $teamlimitQuery->where('series_id', $series_id)->get()->toArray();

        return $teamlimits;
    }

    public function createTeamLimit(int $series_id): bool
    {
        $ageGroups = AgeGroup::all();

        return DB::transaction(function() use($series_id, $ageGroups) {
            foreach ($ageGroups as $ageGroup) {
                $teamLimit = new TeamLimit();
                $teamLimit->series_id = $series_id;
                $teamLimit->save();

                $teamLimit->ageGroups()->attach($ageGroup->id);
            }
            return true;
        });
    }

    public function updateTeamLimit(array $teamcounts): bool
    {
        foreach ($teamcounts as $id => $teamcount) {
            $teamLimit = $this->find($teamcount['id']);

            $teamLimit->team_limit = $teamcount['teamcount'];
            $teamLimit->is_selected = $teamcount['selected'];

            DB::transaction(function () use ($teamLimit) {
                $teamLimit->save();
            });
        }

        return true;
    }

    public function deleteTeamLimit(int $id): bool
    {
        $teamLimit = $this->find($id);

        return DB::transaction(function() use($teamLimit) {

            return $teamLimit->delete();
        });
    }

}
