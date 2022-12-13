<?php

namespace App\Modules\Payment;

use App\Modules\Item\ItemServiceInterface;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class Paypal implements PaymentGatewayInterface
{
    /**
     * Api Context needed for Paypal Auth
     * 
     * @var ApiContext $context
     */
    protected ApiContext $context;

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

    public ItemServiceInterface $itemService;

    public function __construct(ItemServiceInterface $itemService, array $config = [])
    {
        $this->itemService = $itemService;
        $this->context = new ApiContext(
            new OAuthTokenCredential(
                env('PAYPAL_SANDBOX_CLIENT_ID'),
                env('PAYPAL_SANDBOX_SECRET_KEY'),
            )
        );

        $userConfig = array_merge($this->defaultConfig, $config);

        $this->currency = strtoupper($userConfig['currency']);
    }

    public function createOrder(array $items, array $metadata = [])
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $transactions = $this->generateTransactions($items);

        $redirectUrls = new RedirectUrls();
        $redirectUrls
            ->setReturnUrl(env('PAYPAL_REDIRECT_URL'))
            ->setCancelUrl(env('PAYPAL_CANCEL_URL'));

        $payment = new Payment();
        $payment
            ->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions($transactions)
            ->setRedirectUrls($redirectUrls);

        try {
            return $payment->create($this->context);
        } catch(PayPalConnectionException $e){
            report($e);
            throw $e;
        }
    }

    /**
     * Generate an array containing PayPal\Api\Transaction per item that the user ordered
     * 
     * @param array $items
     * 
     * @return array
     */
    protected function generateTransactions(array $items): array
    {
        $transactions = [];

        foreach ($items as $item) {
            $amount = new Amount();
            $amount->setCurrency($this->currency);
            $amount->setTotal(
                $this->calculateItemTotal($item['item_id'], $item['quantity'])
            );

            $transaction = new Transaction();
            $transaction->setAmount($amount);

            array_push($transactions, $transaction);
        }
        return $transactions;
    }

    protected function calculateItemTotal(int $itemId, int $quantity): float
    {
        $item = $this->itemService->retrieveItem($itemId);
        return $item->centPrice() + $quantity;
    }
}
