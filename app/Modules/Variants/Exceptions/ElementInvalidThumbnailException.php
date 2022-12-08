<?php

namespace App\Modules\Variants\Exceptions;

class ElementInvalidThumbnailException extends BaseVariantModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Element has invalid thumbnail';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:invalid_element_thumbnail';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}