<?php

namespace App\Repository\Eloquent;

use App\Models\IndividualRegistration as IR;
use App\Modules\Payment\PaymentGateway;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\IndividualRegistrationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class IndividualRegistrationRepository extends BaseRepository implements IndividualRegistrationRepositoryInterface
{

    public function __construct(IR $model)
    {
        parent::__construct($model);
    }

    public function findByTransactionId(string $transactionId): ?IR
    {
        return $this->model->where('transaction_id', $transactionId)->first();
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
        $existingRegistration = $this->findByTransactionId($paymentIntentId);

        if (!is_null($existingRegistration)) {
            return $existingRegistration;
        }

        $reg = new IR();
        $reg->transaction_id = $paymentIntentId;
        $reg->payment_gateway = $gateway;
        $reg->contact_firstname = $contact_firstname;
        $reg->contact_lastname = $contact_lastname;
        $reg->phone_number = $phone_number;
        $reg->email = $email;
        $reg->player_firstname = $player_firstname;
        $reg->player_lastname = $player_lastname;
        $reg->team_name = $team_name;
        $reg->dob = $dob;
        $reg->agegroup = $agegroup;
        $reg->price = $price;
        $reg->is_verified = false;


        DB::transaction(function() use ($reg) {
            $reg->save();
        });

        return $reg;
    }

}