<?php

namespace App\Modules\Auth\Exceptions;

use App\Modules\Support\Exception;

/**
 * Base Exception for Booking Module
 */
class RequestThrottledException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Request throttled. Retry again in a few minutes';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:auth_module_request_throttled';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 429;
}