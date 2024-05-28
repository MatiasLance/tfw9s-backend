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
     * @param string $team_name
     * @param string $dob
     * @param string $agegroup
     * @param integer $price
     * 
     * @return true|IndividualRegistration Returns true if the IndividualRegistration is already existing, otherwise returns the IndividualRegistration
     */
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
    );
}
