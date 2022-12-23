<?php

namespace App\Modules\Tags;

use App\Models\Tag;
use App\Models\User;
use App\Repository\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TagService implements TagServiceInterface
{
    /**
     * Tag Repository
     * 
     * @var TagRepositoryInterface $tagRepository
     */
    protected TagRepositoryInterface $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function listTags(): Collection
    {
        return $this->tagRepository->list();
    }

    public function retrieveTag(int $id): Tag
    {
        // placeholder
        return new Tag();
    }

    public function createTag(string $name): Tag
    {
        // placeholder
        return new Tag();
    }

    public function updateTag(int $Id, string $name): bool
    {
        // placeholder
        return false;
    }

    public function deleteTag(User $initiator, int $Id): bool
    {
        // placeholder
        return false;
    }
}