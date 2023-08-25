<?php

namespace App\Modules\Tags;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface TagServiceInterface
{
    /**
     * List all available item tags
     * 
     * @return Collection<Tag>
     */
    public function listTags(): Collection;

    /**
     * Retrieve an existing item tag
     * 
     * @param int $id ID of the item tag
     * 
     * @return Tag
     */
    public function retrieveTag(int $id): Tag;

    /**
     * Create a new item Tag
     * 
     * @param string $name
     * 
     * @return Tag
     */
    public function createTag(string $name): Tag;

    /**
     * Update an existing tag
     * 
     * @param int $id ID of the tag to update
     * @param string $name
     * 
     * @return bool
     */
    public function updateTag(int $Id, string $name): bool;

    /**
     * Delete an existing item tag
     * 
     * @param User $initiator
     * @param int $id ID of the tag to be deleted
     * 
     * @return bool
     */
    public function deleteTag(User $initiator, int $Id): bool;
}