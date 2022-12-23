<?php

namespace App\Modules\Payment\Exceptions;

class UnknownPaymentStatusException extends BasePaymentModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Payment status given unknown';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:payment_status_unknown';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}