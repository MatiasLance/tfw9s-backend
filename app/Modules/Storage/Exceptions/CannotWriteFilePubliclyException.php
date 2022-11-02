<?php

namespace App\Modules\Storage\Exceptions;

/**
 * Thrown when an error occurs during writing a file publicly
 */
class CannotWriteFilePubliclyException extends BaseStorageModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Cannot write file';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:cannot_write_file';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 503;

    /**
     * Response detail
     * 
     * @var string $detail
     */
    protected string $detail = 'Server failed to save file. Please try again.';
}