<?php

namespace App\Repository;

use App\Models\IndividualRegistration;
use App\Modules\Payment\PaymentGateway;
use DateTime;

interface IndividualRegistrationRepositoryInterface
{

    /**
     * Find an existing order by its transaction id
     *
     * @param string $transactionId
     *
     * @return null|IndividualRegistration
     */
    public function findByTransactionId(string $transactionId): ?IndividualRegistration;

    /**
     * Create a new IndividualRegistration
     *
     * @param string $paymentIntentId
     * @param PaymentGateway $gateway
     * @param string $contactEmail
     * @param string $contactFirstName
     * @param string $contactLastName
     * @param string $contactPhoneNumber
     * @param string $dob
     * @param string $playerFirstName
     * @param string $playerLastName
     * @param int $team
     * @param string $agegroup
     * @param int $item_id
     *
     * @return true|IndividualRegistration Returns true if the IndividualRegistration is already existing, otherwise returns the IndividualRegistration
     */
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
    );


    /**
     * Mark order as verified
     *
     * @param string $transactionId
     *
     * @return bool
     */
    public function markAsVerified(string $transactionId): bool;
}