<?php

namespace App\Modules\Item\Exceptions;

class CategoryCannotLoopException extends BaseItemModuleException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Catagories cannot be moved underneath itself or its subcategories';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:category_cannot_loop';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;
}