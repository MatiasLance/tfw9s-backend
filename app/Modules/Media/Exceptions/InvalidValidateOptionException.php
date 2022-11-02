<?php

namespace App\Modules\Media\Exceptions;

/**
 * Thrown when validating media and option given is invalid
 * 
 * @see MediaTypes For list of valid options
 */
class InvalidValidateOptionException extends BaseMediaModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Cannot validate media';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:invalid_validate_option';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;

    /**
     * Response detail
     * 
     * @var string $detail
     */
    protected string $detail = 'Invalid validate option given.';
}