<?php

namespace App\Modules\Field;

use App\Models\Field;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface FieldServiceInterface
{
    /**
     * Retrieve a list of fields
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Field>
     */
    public function listFields(array $filters = []): Paginate;

    /**
     * Retrieve an Field
     *
     * @param int $id
     *
     * @return Field
     */
    public function retrieveField(int $id): Field;

    /**
     * Create a new Field
     *
     * @param string $name
     * @param string $description
     * @param int $field_id
     *
     * @return Field
     */
    public function createField(string $name, string $description, int $field_id): Field;

    /**
     * Update an existing Field
     *
     * @param int $id
     * @param string $name
     * @param string $description
     * @param int $field_id
     *
     * @return bool
     */
    public function updateField(int $id, string $name, string $description, int $field_id): bool;

    /**
     * Delete an existing Field
     *
     * @param User $initiator The user who initiated the delete command
     * @param Field $field The field to be deleted
     *
     * @return bool
     */
    public function deleteField(User $initiator, Field $field): bool;

}
