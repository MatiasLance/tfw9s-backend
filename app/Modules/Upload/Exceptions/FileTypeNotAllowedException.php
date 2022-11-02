<?php

namespace App\Modules\Upload\Exceptions;

class FileTypeNotAllowedException extends BaseUploadException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'File type is not allowed';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:file_type_not_allowed';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 403;
}