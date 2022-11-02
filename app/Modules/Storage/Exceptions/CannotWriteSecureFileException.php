<?php

namespace App\Modules\Storage\Exceptions;

/**
 * Thrown when saving a file to a secure container fails.
 */
class CannotWriteSecureFileException extends BaseStorageModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Cannot save secure file';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:cannot_write_secure';

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
    protected string $detail = 'Server failed to save secure file. Please try again.';
}
