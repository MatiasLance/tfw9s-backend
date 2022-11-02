<?php

namespace App\Modules\Http\Exceptions;

use App\Modules\Support\Exception;

class NoErrorCodeException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Exception has no error code';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:exception_no_error_code';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}