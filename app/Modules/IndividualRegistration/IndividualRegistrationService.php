<?php

namespace App\Modules\IndividualRegistration;

use App\Models\IndividualRegistration;
use App\Modules\Payment\PaymentGateway;
use App\Repository\IndividualRegistrationRepositoryInterface;
use DateTime;
use PhpParser\Node\Expr\Cast\Object_;

class IndividualRegistrationService implements IndividualRegistrationServiceInterface
{

    /**
     * IndividualRegistration Repository
     *
     * @var IndividualRegistrationRepositoryInterface $individualRegistrationRepository
     */
    protected IndividualRegistrationRepositoryInterface $individualRegistrationRepository;

    public function __construct(IndividualRegistrationRepositoryInterface $individualRegistrationRepository)
    {
        $this->individualRegistrationRepository = $individualRegistrationRepository;
    }

    public function findByTransactionId(string $transactionId): ?IndividualRegistration
    {
        return $this->individualRegistrationRepository->findByTransactionId($transactionId);
    }

    public function create(
        string $paymentIntentId,
        PaymentGateway $gateway,
        string $contactFirstName,
        string $contactLastName,
        string $contactPhoneNumber,
        string $contactEmail,
        string $playerFirstName,
        string $playerLastName,
        string $dob,
        int $team,
        string $ageGroup,
        int $amount,
        int $item_id
    )
    {
        return $this->individualRegistrationRepository->create(
          $paymentIntentId,
          $gateway,
          $contactFirstName,
          $contactLastName,
          $contactPhoneNumber,
          $contactEmail,
          $playerFirstName,
          $playerLastName,
          $dob,
          $team,
          $ageGroup,
          $amount,
          $item_id
        );
    }

    public function markAsVerified(string $transactionId): bool
    {
        return $this->individualRegistrationRepository->markAsVerified($transactionId);
    }
}