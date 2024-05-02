<?php

namespace App\Modules\Guideline;

use App\Models\User;
use App\Models\Guideline;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\GuidelineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GuidelineService implements GuidelineServiceInterface
{
    /**
     * Guideline Repository
     *
     * @var GuidelineRepositoryInterface $guidelineRepository
     */
    protected GuidelineRepositoryInterface $guidelineRepository;

    public function __construct(GuidelineRepositoryInterface $guidelineRepository)
    {
        $this->guidelineRepository = $guidelineRepository;
    }

    public function listGuidelines(array $filters = []): Paginate
    {
        return $this->guidelineRepository->listGuidelines($filters);
    }

    public function retrieveGuideline(int $id): Guideline
    {
        return $this->guidelineRepository->retrieveGuideline($id);
    }

    public function createGuideline( string $type, string $content): Guideline
    {
        return $this->guidelineRepository->createGuideline($type, $content);
    }

    public function updateGuideline(int $id, string $type, string $content): bool
    {
        return $this->guidelineRepository->updateGuideline($id, $type, $content);
    }

    public function setActive(int $id): bool
    {
        return $this->guidelineRepository->setActive($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->guidelineRepository->deactivate($id);
    }

    public function deleteGuideline(User $initiator, Guideline $guideline): bool
    {
        return $this->guidelineRepository->deleteGuideline($guideline->id);
    }
}
