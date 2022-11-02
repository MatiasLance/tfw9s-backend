<?php

namespace App\Modules\Upload\Exceptions;

use App\Modules\Support\Exception;

class BaseUploadException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the upload module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_upload_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}