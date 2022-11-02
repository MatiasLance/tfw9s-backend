<?php

namespace App\Modules\User\Exceptions;

use Throwable;

/**
 * Thrown when parameter in user api endpoints are invalid
 */
class InvalidUserParameterException extends BaseUserModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Parameter input is invalid';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:user_parameter_invalid';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->setDetail($message);
    }
}