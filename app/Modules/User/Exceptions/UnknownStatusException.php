<?php

namespace App\Modules\User\Exceptions;

/**
 * Thrown when a status given is not a valid/ known status value
 */
class UnknownStatusException extends BaseUserModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Unknown status value';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:unknown_user_status_value';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}