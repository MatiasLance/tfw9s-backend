<?php

namespace Tests\Feature\Registration;

use App\Models\IndividualRegistration;
use App\Models\Player;
use App\Modules\IndividualRegistration\IndividualRegistrationServiceInterface;
use App\Modules\IndividualRegistration\RegistrationIdentity;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Mail\MailServiceInterface;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Payment\Gateways\Stripe as StripeGateway;
use App\Modules\Payment\PaymentGateway;
use App\Modules\Payment\PaymentServiceInterface;
use App\Modules\Payment\PaymentStatus;
use App\Modules\TeamRegistration\TeamRegistrationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Stripe\PaymentIntent;
use Tests\TestCase;

class RegistrationFlowSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_age_group_is_rejected_before_payment_creation(): void
    {
        $seriesId = $this->createWeeklySeries();
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldNotReceive('createIndividualRegistration');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        $this->postJson('/api/v1/tournament/indiv/checkout', [
            'item' => $seriesId,
            'payment_method' => 'stripe',
            'metadata' => $this->registrationMetadata(['ageGroup' => 'U10']),
            'discountcode' => null,
            'lounge_token' => 'not-reached',
            'client_id' => 'client-1',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('metadata.ageGroup');
    }

    public function test_repository_replay_creates_one_registration_and_player(): void
    {
        [$seriesId, $teamId, $ageGroupId] = $this->createWeeklySeriesWithTeam();
        $service = app(IndividualRegistrationServiceInterface::class);
        $metadata = $this->registrationMetadata([
            'teamName' => $teamId,
            'ageGroup' => $ageGroupId,
        ]);
        $registrationKey = RegistrationIdentity::make($seriesId, $metadata);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $service->create(
                'pi_replayed',
                PaymentGateway::STRIPE,
                $metadata['contactFirstName'],
                $metadata['contactLastName'],
                $metadata['contactPhoneNumber'],
                $metadata['contactEmail'],
                $metadata['playerFirstName'],
                $metadata['playerLastName'],
                $metadata['dob'],
                (string) $teamId,
                (string) $ageGroupId,
                200,
                $seriesId,
                $registrationKey,
            );
        }

        $this->assertSame(1, IndividualRegistration::count());
        $this->assertSame(1, Player::count());
        $this->assertSame(4, (int) DB::table('teams')->where('id', $teamId)->value('player_limit'));
    }

    public function test_successful_verification_is_replay_safe_when_invoice_delivery_fails(): void
    {
        [$seriesId, $teamId, $ageGroupId] = $this->createWeeklySeriesWithTeam();
        $metadata = $this->registrationMetadata([
            'teamName' => $teamId,
            'ageGroup' => $ageGroupId,
        ]);
        $metadata['registration_key'] = RegistrationIdentity::make($seriesId, $metadata);
        $metadata['line_item'] = json_encode(['item_id' => $seriesId, 'price' => 200]);

        $intent = PaymentIntent::constructFrom([
            'id' => 'pi_verified_once',
            'amount' => 200,
            'status' => PaymentIntent::STATUS_SUCCEEDED,
            'metadata' => $metadata,
        ]);

        $mail = Mockery::mock(MailServiceInterface::class);
        $mail->shouldReceive('sendIndividualRegistrationInvoice')
            ->once()
            ->andThrow(new \RuntimeException('mail relay unavailable'));

        $gateway = new class(
            $mail,
            app(OrderServiceInterface::class),
            app(IndividualRegistrationServiceInterface::class),
            app(TeamRegistrationServiceInterface::class),
            app(ItemServiceInterface::class),
            $intent
        ) extends StripeGateway {
            private PaymentIntent $fakeIntent;

            public function __construct(
                MailServiceInterface $mail,
                OrderServiceInterface $order,
                IndividualRegistrationServiceInterface $individual,
                TeamRegistrationServiceInterface $team,
                ItemServiceInterface $item,
                PaymentIntent $intent
            ) {
                $this->mailService = $mail;
                $this->orderService = $order;
                $this->individualRegistrationService = $individual;
                $this->teamRegistrationService = $team;
                $this->itemService = $item;
                $this->currency = 'aud';
                $this->gst = '10';
                $this->fakeIntent = $intent;
            }

            protected function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
            {
                return $this->fakeIntent;
            }
        };

        $this->assertSame(PaymentStatus::COMPLETE, $gateway->verifyIndividualRegistration($intent->id));
        $this->assertSame(PaymentStatus::COMPLETE, $gateway->verifyIndividualRegistration($intent->id));
        $this->assertSame(1, IndividualRegistration::count());
        $this->assertSame(1, Player::count());
        $this->assertTrue(IndividualRegistration::firstOrFail()->is_verified);
        $this->assertSame(4, (int) DB::table('teams')->where('id', $teamId)->value('player_limit'));
    }

    private function createWeeklySeries(): int
    {
        return DB::table('series')->insertGetId([
            'name' => 'Central Coast Weekly Series',
            'type' => 'weekly',
            'description' => 'Registration test series',
            'price' => 200,
            'is_paused' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createWeeklySeriesWithTeam(): array
    {
        $seriesId = $this->createWeeklySeries();
        $ageGroupId = DB::table('age_groups')->insertGetId([
            'name' => 'U10',
            'min_age' => 9,
            'max_age' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $teamId = DB::table('teams')->insertGetId([
            'name' => 'Central Coast Team',
            'agegroup_id' => $ageGroupId,
            'series_id' => $seriesId,
            'player_limit' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$seriesId, $teamId, $ageGroupId];
    }

    private function registrationMetadata(array $overrides = []): array
    {
        return array_merge([
            'contactFirstName' => 'Client',
            'contactLastName' => 'Test',
            'contactPhoneNumber' => '0400000000',
            'contactEmail' => 'client@example.test',
            'playerFirstName' => 'Weekly',
            'playerLastName' => 'Player',
            'dob' => '2016-01-15',
            'teamName' => 1,
            'ageGroup' => 1,
            'discountCodeId' => null,
        ], $overrides);
    }
}
