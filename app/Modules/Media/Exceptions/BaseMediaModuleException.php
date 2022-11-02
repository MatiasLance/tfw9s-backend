<?php

namespace App\Modules\Media\Exceptions;

use App\Modules\Support\Exception;

/**
 * Base Exception for Media Module
 */
class BaseMediaModuleException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the Media module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_media_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}