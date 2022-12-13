<?php

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\PaymentServiceInterface;

class BasePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Default configuration values
     *
     * @var array $defaultConfig
     */
    protected array $defaultConfig = [
        /**
         * ISO-3 currency code to use
         * 
         * @var string
         */
        'currency' => PaymentServiceInterface::CURRENCY
    ];

    /**
     * ISO-3 currency code to use
     * 
     * @var string
     */
    protected string $currency;

    public function __construct(array $config = [])
    {
        $userConfig = array_merge($config, $this->defaultConfig);

        $this->currency = $userConfig['currency'];
    }

    public function createOrder(array $items, array $metadata = [])
    {
        //
    }

    public function verify(string $transactionId)
    {
        //
    }
}