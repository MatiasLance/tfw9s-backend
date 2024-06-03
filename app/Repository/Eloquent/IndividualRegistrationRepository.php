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

    public function create(string $paymentIntentId, PaymentGateway $gateway, string $contactEmail, string $contactFirstName, string $contactLastName, string $contactPhoneNumber, string $playerFirstName, string $playerLastName, string $dob, string $teamName, string $ageGroup, int $amount, int $item_id)
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
        $player->email = $contactEmail;
        $player->contact_firstname = $contactFirstName;
        $player->contact_lastname = $contactLastName;
        $player->phone_number = $contactPhoneNumber;
        $player->player_firstname = $playerFirstName;
        $player->player_lastname = $playerLastName;
        $player->team_name = $teamName;
        $player->dob = $dob;
        $player->agegroup = $ageGroup;

        DB::transaction(function() use ($reg, $player) {
            $reg->save();
            $player->registration_id = $reg->id;
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
