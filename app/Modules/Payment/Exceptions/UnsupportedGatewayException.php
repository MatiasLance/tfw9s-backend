<?php

namespace App\Modules\Payment\Exceptions;

class UnsupportedGatewayException extends BasePaymentModuleExecption
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Payment gateway given is unsupported';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:unsupported_payment_gateway';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}