<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    /**
     * Retrieve list of all tags
     * 
     * @return Collection<Tag>
     */
    public function list(): Collection;
}