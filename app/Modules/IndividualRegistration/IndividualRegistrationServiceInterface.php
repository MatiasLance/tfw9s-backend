<?php

namespace App\Modules\IndividualRegistration;

use App\Models\IndividualRegistration;
use App\Modules\Payment\PaymentGateway;

interface IndividualRegistrationServiceInterface
{

    /**
     * Find an existing IndividualRegistration by its transaction ID
     *
     * @param String $transactionId
     *
     * @return null|IndividualRegistration
     */
    public function findByTransactionId(string $transactionId): ?IndividualRegistration;

      /**
     * Create a new IndividualRegistration
     *
     * @param string $paymentIntentId
     * @param PaymentGateway $gateway
     * @param string $contact_firstname
     * @param string $contact_lastname
     * @param string $phone_number
     * @param string $email
     * @param string $player_firstname
     * @param string $player_lastname
     * @param int $team
     * @param string $dob
     * @param string $agegroup
     * @param integer $price
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