<?php

namespace App\Modules\Payment;

use App\Modules\Payment\Exceptions\UnsupportedGatewayException;
use App\Modules\Payment\Gateways\PaymentGatewayInterface;
use Illuminate\Support\Facades\App;
use Psy\Exception\TypeErrorException;

class PaymentService implements PaymentServiceInterface
{
    public function createOrder($discountcode, string $gateway, array $items , array $metadata = [], $currency = null)
    {
        $config = [
            'currency' => $currency
        ];

        $paymentGateway = $this->getGateway($gateway, $config);
        return $paymentGateway->createOrder($discountcode, $items, $metadata);
    }

    public function verify(string $gateway, string $transactionId)
    {
        $paymentGateway = $this->getGateway($gateway);
        return $paymentGateway->verify($transactionId);
    }

    public function createIndividualRegistration($discountcode, string $gateway, string $item, array $metadata = [], $currency = null)
    {
        $config = [
            'currency' => $currency
        ];

        $paymentGateway = $this->getGateway($gateway, $config);
        return $paymentGateway->createIndividualRegistration($discountcode, $item, $metadata);
    }

    public function createTeamRegistration($discountcode, string $gateway, string $item, array $metadata = [], string $token)
    {
        $config = [
            'currency' => 'aud'
        ];

        $paymentGateway = $this->getGateway($gateway, $config);
        return $paymentGateway->createTeamRegistration($discountcode, $item, $metadata, $token);
    }

    public function verifyIndividualRegistration(string $gateway, string $transactionId)
    {
        $paymentGateway = $this->getGateway($gateway);
        return $paymentGateway->verifyIndividualRegistration($transactionId);
    }


    public function verifyTeamRegistration(string $gateway, string $transactionId)
    {
        $paymentGateway = $this->getGateway($gateway);
        return $paymentGateway->verifyTeamRegistration($transactionId);
    }

    /**
     * Payment gateway factory method
     *
     * @see App\Modules\Payment\PaymentGateway See for list of supported gateways
     *
     * @param string $gateway The unique identifier for the gateway to use.
     * @param array $config Config values to pass to the Payment gateway class
     *
     * @return App\Modules\Payment\Gateways\PaymentGatewayInterface
     */
    protected function getGateway(string $gateway, array $config = []): PaymentGatewayInterface
    {
        try {
            $paymentGateway = PaymentGateway::from($gateway);
        }catch(TypeErrorException $e) {
            throw new UnsupportedGatewayException($gateway . ' payment gateway is unsupported.');
        }

        return App::makeWith($paymentGateway->getGatewayClass(), ['config' => $config]);
    }

    public function updateAmount(string $paymentIntent, array $updateParams, string $gateway)
    {
        $paymentGateway = $this->getGateway($gateway);
        return $paymentGateway->updateAmount($paymentIntent, $updateParams);
    }

    public function registrationRefund(string $method, string $transaction_id, int $amount)
    {
        $paymentGateway = $this->getGateway($method);
        return $paymentGateway->registrationRefund($transaction_id, $amount);
    }

    public function cancelRefund(string $method, string $transaction_id)
    {
        $paymentGateway = $this->getGateway($method);
        return $paymentGateway->cancelRefund($transaction_id);
    }
}
