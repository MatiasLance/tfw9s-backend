<?php

namespace App\Repository\Eloquent;

use App\Models\IndividualRegistration;
use App\Models\Player;
use App\Models\Team;
use App\Modules\Payment\PaymentGateway;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\IndividualRegistrationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

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
        string $team,
        string $ageGroup,
        int $amount,
        int $item_id,
        ?string $registrationKey = null,
    ) {
        try {
            return DB::transaction(function () use (
                $paymentIntentId, $gateway, $contactFirstName, $contactLastName,
                $contactPhoneNumber, $contactEmail, $playerFirstName,
                $playerLastName, $dob, $team, $ageGroup, $amount, $item_id,
                $registrationKey,
            ) {
                $existingRegistration = $this->model
                    ->where('transaction_id', $paymentIntentId)
                    ->when($registrationKey, function ($query) use ($registrationKey) {
                        $query->orWhere('registration_key', $registrationKey);
                    })
                    ->lockForUpdate()
                    ->first();

                if ($existingRegistration) {
                    return $existingRegistration;
                }

                $reg = new IndividualRegistration();
                $reg->transaction_id = $paymentIntentId;
                $reg->registration_key = $registrationKey;
                $reg->payment_gateway = $gateway;
                $reg->email = $contactEmail;
                $reg->price = $amount;
                $reg->item_id = $item_id;
                $reg->is_verified = false;
                $reg->save();

                $player = new Player();
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

                $teamModel = Team::where('id', $team)->lockForUpdate()->first();

                if (!$teamModel) {
                    throw new \RuntimeException('Team not found.');
                }

                if ($teamModel->player_limit <= 0) {
                    throw new \RuntimeException('No available slots in this team.');
                }

                $teamModel->decrement('player_limit');

                return $reg;
            });
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw $exception;
            }

            $existingRegistration = $this->model
                ->where('transaction_id', $paymentIntentId)
                ->when($registrationKey, function ($query) use ($registrationKey) {
                    $query->orWhere('registration_key', $registrationKey);
                })
                ->first();

            if (! $existingRegistration) {
                throw $exception;
            }

            return $existingRegistration;
        }
    }

    public function markAsVerified(string $transactionId): bool
    {
        return DB::transaction(function () use ($transactionId) {
            $seriesRegistration = $this->model
                ->where('transaction_id', $transactionId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($seriesRegistration->is_verified) {
                return true;
            }

            $seriesRegistration->is_verified = true;
            return $seriesRegistration->save();
        });
    }

}
