<?php

namespace App\Modules\Upload\Exceptions;

class FileNotFoundException extends BaseUploadException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'File was not found';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:file_was_not_found';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}