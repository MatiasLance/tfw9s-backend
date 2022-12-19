<?php

namespace App\Modules\Payment;

use App\Modules\Payment\Exceptions\UnsupportedGatewayException;
use App\Modules\Payment\Gateways\PaymentGatewayInterface;
use App\Modules\Payment\Gateways\Paypal;
use App\Modules\Payment\Gateways\Stripe;
use Illuminate\Support\Facades\App;

class PaymentService implements PaymentServiceInterface
{
    public function createOrder(string $gateway, array $items, array $metadata = [], $currency = null)
    {
        $config = [
            'currency' => $currency
        ];

        $paymentGateway = $this->getGateway($gateway, $config);
        return $paymentGateway->createOrder($items, $metadata);
    }

    public function verify(string $gateway, string $transactionId)
    {
        $paymentGateway = $this->getGateway($gateway);
        return $paymentGateway->verify($transactionId);
    }

    /**
     * Payment gateway factory method
     * 
     * @see App\Modules\Payment\Gateways\PaymentGatewayInterface See for list of supported gateways
     * 
     * @param string $gateway The unique identifier for the gateway to use.
     * @param array $config Config values to pass to the Payment gateway class
     * 
     * @return App\Modules\Payment\Gateways\PaymentGatewayInterface
     */
    protected function getGateway(string $gateway, array $config = []): PaymentGatewayInterface
    {
        switch ($gateway) {
            case PaymentGatewayInterface::PAYMENT_METHOD_STRIPE:
                return App::makeWith(Stripe::class, ['config' => $config]);

            case PaymentGatewayInterface::PAYMENT_METHOD_PAYPAL:
                return App::makeWith(Paypal::class, ['config' => $config]);
            
            default:
                throw new UnsupportedGatewayException($gateway . ' payment gateway is unsupported.');
                break;
        }
    }
}
