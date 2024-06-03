<?php

namespace App\Modules\TeamRegistration;

use App\Models\TeamRegistration;
use App\Modules\Payment\PaymentGateway;
use App\Repository\TeamRegistrationRepositoryInterface;
use DateTime;

class TeamRegistrationService implements TeamRegistrationServiceInterface
{

    /**
     * TeamRegistration Repository
     *
     * @var TeamRegistrationRepositoryInterface $teamRegistrationRepository
     */
    protected TeamRegistrationRepositoryInterface $teamRegistrationRepository;

    public function __construct(TeamRegistrationRepositoryInterface $teamRegistrationRepository)
    {
        $this->teamRegistrationRepository = $teamRegistrationRepository;
    }

    public function findByTransactionId(string $transactionId): ?TeamRegistration
    {
        return $this->teamRegistrationRepository->findByTransactionId($transactionId);
    }

    public function create(
        string $paymentIntentId,
        PaymentGateway $gateway,
        string $coachesEmail,
        string $coachesName,
        string $coachesPhoneNumber,
        string $managerEmail,
        string $managerName,
        string $managerPhoneNumber,
        string $teamName,
        string $ageGroup,
        int $amount,
        int $item_id
    )
    {
        return $this->teamRegistrationRepository->create(
            $paymentIntentId,
            $gateway,
            $coachesEmail,
            $coachesName,
            $coachesPhoneNumber,
            $coachesEmail,
            $coachesName,
            $coachesPhoneNumber,
            $teamName,
            $ageGroup,
            $amount,
            $item_id
        );
    }

    public function markAsVerified(string $transactionId): bool
    {
        return $this->teamRegistrationRepository->markAsVerified($transactionId);
    }
}
