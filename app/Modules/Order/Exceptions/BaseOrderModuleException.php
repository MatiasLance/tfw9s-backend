<?php

namespace App\Modules\Order\Exceptions;

use App\Modules\Support\Exception;

class BaseOrderModuleException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the Order module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_order_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}