<?php

namespace App\Modules\Item\Exceptions;

class ItemStockCannotBeLowerThanZeroException extends BaseItemModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Item stock cannot go lower than 0';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:item_stock_cannot_be_lower_than_zero';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}