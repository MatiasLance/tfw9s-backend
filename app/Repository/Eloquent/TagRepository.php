<?php

namespace App\Repository\Eloquent;

use App\Models\Tag;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $tag)
    {
        parent::__construct($tag);
    }

    public function list(): Collection
    {
        return $this->all();
    }
}