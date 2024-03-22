<?php

namespace App\Modules\Field;

use App\Models\User;
use App\Models\Field;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\FieldRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FieldService implements FieldServiceInterface
{
    /**
     * Field Repository
     *
     * @var FieldRepositoryInterface $fieldRepository
     */
    protected FieldRepositoryInterface $fieldRepository;

    public function __construct(FieldRepositoryInterface $fieldRepository)
    {
        $this->fieldRepository = $fieldRepository;
    }

    public function listFields(array $filters = []): Paginate
    {
        return $this->fieldRepository->listFields($filters);
    }

    public function retrieveField(int $id): Field
    {
        return $this->fieldRepository->retrieveField($id);
    }

    public function createField($name, $description, $region_id): Field
    {
        return $this->fieldRepository->createField($name, $description, $region_id);
    }

    public function updateField(int $id, string $name, string $description, $region_id): bool
    {
        return $this->fieldRepository->updateField($id, $name, $description, $region_id);
    }

    public function deleteField(User $initiator, Field $field): bool
    {
        return $this->fieldRepository->deleteField($field->id);
    }
}
