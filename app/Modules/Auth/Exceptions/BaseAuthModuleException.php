<?php

namespace App\Modules\Auth\Exceptions;

use App\Modules\Support\Exception;

/**
 * Base Exception for Booking Module
 */
class BaseAuthModuleException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the Auth module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_auth_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}