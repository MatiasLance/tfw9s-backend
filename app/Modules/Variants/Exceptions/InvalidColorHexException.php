<?php

namespace App\Modules\Variants\Exceptions;

class InvalidColorHexException extends BaseVariantModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Invalid color hex provided';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:invalid_color_hex';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}