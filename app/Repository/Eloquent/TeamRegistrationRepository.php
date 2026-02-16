<?php

namespace App\Repository\Eloquent;

use App\Models\TeamRegistration;
use App\Models\Team;
use App\Models\TeamLimit;
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
        return $this->model
            ->where('transaction_id', $transactionId)
            ->first();
    }

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
    ): TeamRegistration {
        $existingRegistration = $this->findByTransactionId($paymentIntentId);

        if ($existingRegistration !== null) {
            return $existingRegistration;
        }

        return DB::transaction(function () use (
            $paymentIntentId,
            $gateway,
            $coachesEmail,
            $coachesName,
            $coachesPhoneNumber,
            $managerEmail,
            $managerName,
            $managerPhoneNumber,
            $teamName,
            $ageGroup,
            $amount,
            $item_id,
        ) {
            $reg = $this->model->create([
                'transaction_id' => $paymentIntentId,
                'payment_gateway' => $gateway->value,
                'coach_email' => $coachesEmail,
                'manager_email' => $managerEmail,
                'price' => $amount,
                'item_id' => $item_id,
                'is_verified' => false,
            ]);

            Team::create([
                'registration_id' => $reg->id,
                'coach_email' => $coachesEmail,
                'coach_name' => $coachesName,
                'coach_mobile' => $coachesPhoneNumber,
                'manager_email' => $managerEmail,
                'manager_name' => $managerName,
                'manager_mobile' => $managerPhoneNumber,
                'series_id' => $item_id,
                'name' => $teamName,
                'agegroup_id' => $ageGroup,
            ]);

            TeamLimit::where('series_id', $item_id)
                ->whereHas('ageGroups', fn($query) => $query->where('agegroup_id', $ageGroup))
                ->increment('teamcount');

            return $reg;
        });
    }

    public function markAsVerified(string $transactionId): bool
    {
        return DB::transaction(function () use ($transactionId) {
            return $this->model
                ->where('transaction_id', $transactionId)
                ->update(['is_verified' => true]) > 0;
        });
    }

}
