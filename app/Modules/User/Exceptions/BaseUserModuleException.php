<?php

namespace App\Modules\User\Exceptions;

use App\Modules\Support\Exception;

/**
 * Base Exception for user module
 */
class BaseUserModuleException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the User module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_user_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}