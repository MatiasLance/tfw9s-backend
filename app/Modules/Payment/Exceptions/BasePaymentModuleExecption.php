<?php

namespace App\Modules\Payment\Exceptions;

use App\Modules\Support\Exception;

class BasePaymentModuleExecption extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the payment module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_payment_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}