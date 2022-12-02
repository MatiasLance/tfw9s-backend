<?php

namespace App\Modules\Variants\Exceptions;

use App\Modules\Support\Exception;

class BaseVariantModuleException extends Exception
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Error in the Variant module';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:base_variant_module_exception';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 500;
}