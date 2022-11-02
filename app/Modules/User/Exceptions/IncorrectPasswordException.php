<?php

namespace App\Modules\User\Exceptions;

/**
 * Thrown when a password check fails.
 * 
 * Example would be changing passwords or changing a critical
 * information that warrants re-entering password for security reasons
 */
class IncorrectPasswordException extends BaseUserModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Password does not match account';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:password_mismatch';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 403;
}