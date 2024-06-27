<?php

namespace App\Modules\Faq;

use App\Models\Faq;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\FaqRepositoryInterface;

class FaqService implements FaqServiceInterface
{
    protected FaqRepositoryInterface $faqRepository;

    public function __construct(FaqRepositoryInterface $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    public function listFaq(array $filters = []): Paginate
    {
        return $this->faqRepository->listFaq($filters);
    }

    public function retrieveFaq(int $id): Faq
    {
        return $this->faqRepository->retrieveFaq($id);
    }

    public function store(string $title, string $description): Faq
    {
        return $this->faqRepository->store($title, $description);
    }

    public function updateFaq(int $id, string $title, string $description,): bool
    {
        return $this->faqRepository->updateFaq($id, $title, $description);
    }

    public function deleteFaq(Faq $faq): bool
    {
        return $this->faqRepository->deleteFaq($faq->id);
    }
}