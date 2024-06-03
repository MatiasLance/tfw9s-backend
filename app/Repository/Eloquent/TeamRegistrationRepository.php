<?php

namespace App\Repository\Eloquent;

use App\Models\TeamRegistration;
use App\Models\Team;
use App\Modules\Payment\PaymentGateway;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\TeamRegistrationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Polyfill\Intl\Idn\Idn;
use DateTime;

class TeamRegistrationRepository extends BaseRepository implements TeamRegistrationRepositoryInterface
{

    public function __construct(TeamRegistration $model)
    {
        parent::__construct($model);
    }

    public function findByTransactionId(string $transactionId): ?TeamRegistration
    {
    return $this->model->where('transaction_id', $transactionId)->first();
    }

    public function create(string $paymentIntentId, PaymentGateway $gateway, string $coachesEmail, string $coachesName, string $coachesPhoneNumber, string $managerEmail, string $managerName, string $managerPhoneNumber, string $teamName, string $ageGroup, int $amount, int $item_id)
    {
        $existingRegistration = $this->findByTransactionId($paymentIntentId);

        if (!is_null($existingRegistration)) {
          return $existingRegistration;
        }

        $reg = new TeamRegistration();
        $reg->transaction_id = $paymentIntentId;
        $reg->payment_gateway = $gateway;
        $reg->coach_email = $coachesEmail;
        $reg->manager_email = $managerEmail;
        $reg->price = $amount;
        $reg->item_id = $item_id;
        $reg->is_verified = false;

        $team = new Team();
        $team->coach_email = $coachesEmail;
        $team->coach_name = $coachesName;
        $team->coach_mobile = $coachesPhoneNumber;
        $team->manager_email = $managerEmail;
        $team->manager_name = $managerName;
        $team->manager_mobile = $managerPhoneNumber;
        $team->name = $teamName;
        $team->agegroup_id = $ageGroup;

        DB::transaction(function() use ($reg, $team) {
            $reg->save();
            $team->registration_id = $reg->id;
            $team->save();
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
