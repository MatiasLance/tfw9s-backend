<?php

namespace App\Modules\Order\Exceptions;

class UnknownShippingTypeException extends BaseOrderModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Unknown shipping type given';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:unknown_shipping_type_given';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}