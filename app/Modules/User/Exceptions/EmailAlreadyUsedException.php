<?php

namespace App\Modules\User\Exceptions;

/**
 * Thrown when storing an email address that is already existing
 */
class EmailAlreadyUsedException extends BaseUserModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Email already used';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:duplicate_email';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}