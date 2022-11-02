<?php

namespace App\Modules\Upload\Exceptions;

class FailedToCreateUniqueTokenException extends BaseUploadException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Failed to create a unique token';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:failed_to_create_unique_token';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}