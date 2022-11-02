<?php

namespace App\Modules\Media\Exceptions;

use Throwable;

/**
 * Thrown when the media is not allowed
 */
class MediaNotAllowedException extends BaseMediaModuleException
{
    /**
     * Data regarding the media
     * 
     * This intended to hold the metadata of each media. An example would be
     * when a user has submitted a number of media and only one of them is
     * invalid. This exception will return the list of media, and which of them
     * has passed. The API consumer will then be able to do more with that data
     * than just returning HTTP status 400.
     * 
     * @var array $data
     */
    protected array $data;

    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = 'Media is not valid';

    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode = 'Err:media_not_valid';

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;

    public function __construct(string $message = '', array $data = [], $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $data, $code, $previous);
        $this->data = $data;
    }
}