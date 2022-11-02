<?php

namespace App\Modules\Order\Exceptions;

/**
 * Thrown when the shipping type of an order is delivery and there is no address provided
 */
class AddressCannotBeEmptyException extends BaseOrderModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Shipping address cannot be empty';

    /**
     * Response detail
     * 
     * @var string $detail
     */
    protected string $detail = 'If the order is a delivery type and not pickup, the shipping address must be provided';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:shipping_address_cannot_be_empty';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}