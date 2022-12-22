<?php

namespace App\Modules\Payment\Exceptions;

class PaymentFailedException extends BasePaymentModuleExecption
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Transaction payment proccess failed';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:transaction_process_failed';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}