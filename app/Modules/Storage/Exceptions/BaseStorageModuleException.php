<?php

namespace App\Modules\Storage\Exceptions;

use App\Modules\Support\Exception;

/**
 * Base Exception for storage module
 */
class BaseStorageModuleException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the Storage module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_storage_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}