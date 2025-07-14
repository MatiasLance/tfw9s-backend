<?php

namespace App\Repository\Eloquent;

use App\Models\IndividualRegistration;
use App\Models\Player;
use App\Modules\Payment\PaymentGateway;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\IndividualRegistrationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Polyfill\Intl\Idn\Idn;
use DateTime;

class IndividualRegistrationRepository extends BaseRepository implements IndividualRegistrationRepositoryInterface
{

    public function __construct(IndividualRegistration $model)
    {
        parent::__construct($model);
    }

    public function findByTransactionId(string $transactionId): ?IndividualRegistration
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }

    public function create(string $paymentIntentId, PaymentGateway $gateway, string $contactFirstName, string $contactLastName, string $contactPhoneNumber, string $contactEmail, string $playerFirstName, string $playerLastName, string $dob, int $team, string $ageGroup, int $amount, int $item_id)
    {
        $existingRegistration = $this->findByTransactionId($paymentIntentId);

        if (!is_null($existingRegistration)) {
          return $existingRegistration;
        }
        
        $reg = new IndividualRegistration();
        $reg->transaction_id = $paymentIntentId;
        $reg->payment_gateway = $gateway;
        $reg->email = $contactEmail;
        $reg->price = $amount;
        $reg->item_id = $item_id;
        $reg->is_verified = false;

        $player = new Player();

        DB::transaction(function() use ($reg, $player, $team, $contactFirstName, $contactLastName, $contactPhoneNumber, $contactEmail, $playerFirstName, $playerLastName, $dob, $ageGroup) {
            $reg->save();
            $player->team_id = $team;
            $player->registration_id = $reg->id;
            $player->contact_firstname = $contactFirstName;
            $player->contact_lastname = $contactLastName;
            $player->phone_number = $contactPhoneNumber;
            $player->email = $contactEmail;
            $player->player_firstname = $playerFirstName;
            $player->player_lastname = $playerLastName;
            $player->dob = $dob;
            $player->agegroup_id = $ageGroup;
            $player->save();
        });

        return $reg;
    }

    public function markAsVerified(string $transactionId): bool
    {
        $seriesRegistration = $this->findByTransactionId($transactionId);
        $seriesRegistration->is_verified = true;

        return DB::transaction(function() use($seriesRegistration) {
            return $seriesRegistration->save();
        });
    }

}