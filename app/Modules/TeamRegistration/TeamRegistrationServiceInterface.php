<?php

namespace App\Modules\TeamRegistration;

use App\Models\TeamRegistration;
use App\Modules\Payment\PaymentGateway;
use DateTime;

interface TeamRegistrationServiceInterface
{

    /**
     * Find an existing TeamRegistration by its transaction ID
     *
     * @param String $transactionId
     *
     * @return null|TeamRegistration
     */
    public function findByTransactionId(string $transactionId): ?TeamRegistration;

      /**
     * Create a new TeamRegistration
     *
     * @param string $paymentIntentId
     * @param PaymentGateway $gateway
     * @param string $coachesEmail
     * @param string $coachesName
     * @param string $coachesPhoneNumber
     * @param string $managerEmail
     * @param string $managerName
     * @param string $managerPhoneNumber
     * @param string $teamName
     * @param string $ageGroup
     * @param integer $price
     * @param int $item_id
     *
     * @return true|TeamRegistration Returns true if the TeamRegistration is already existing, otherwise returns the IndividualRegistration
     */
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
