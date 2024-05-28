<?php

namespace App\Modules\IndividualRegistration;

use App\Models\IndividualRegistration;
use App\Modules\Payment\PaymentGateway;
use App\Repository\IndividualRegistrationRepositoryInterface;

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
        string $contact_firstname,
        string $contact_lastname,
        string $phone_number,
        string $email,
        string $player_firstname,
        string $player_lastname,
        string $team_name,
        string $dob,
        string $agegroup,
        int $price,
    )

    {
        return $this->orderRepository->create(
            $paymentIntentId,
            $gateway,
            $contact_firstname,
            $contact_lastname,
            $phone_number,
            $email,
            $player_firstname,
            $player_lastname,
            $team_name,
            $dob,
            $agegroup,
            $price
        );
    }
}