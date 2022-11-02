<?php

namespace App\Modules\Support;

use App\Modules\Support\Exceptions\NoErrorCodeException;
use App\Modules\Http\Problem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception as PhpException;

/**
 * Base exception for custom exceptions that complies with RFC7807
 * 
 * @see https://datatracker.ietf.org/doc/html/rfc7807
 */
class Exception extends PhpException
{
    /**
     * Response title
     * 
     * @var string $title
     */
    protected string $title = '';
    
    /**
     * Response unique errorCode
     * 
     * @var string $errorCode
     */
    protected string $errorCode;

    /**
     * HTTP Status Code for the response
     * 
     * @var int $status
     */
    protected int $status = 400;

    /**
     * Response detail
     * 
     * @var string $detail
     */
    protected string $detail = '';
    
    /**
     * Response type check RFC7807
     * 
     * @see https://datatracker.ietf.org/doc/html/rfc7807
     * 
     * @var string $type
     */
    protected string $type = 'about:blank';

    /**
     * Miscellaneous data to send with the response
     * 
     * @var array $data
     */
    protected array $data = [];

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request)
    {
        return $this->handle($request);
    }

    /**
     * Handle the exception and return a RFC7807 compliant response
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request): Response
    {
        $problem = new Problem();
        $problem->setTitle($this->getTitle());
        $problem->setErrorCode($this->getErrorCode());
        $problem->setStatus($this->getStatus());
        $problem->setDetail($this->getDetail());
        $problem->setType($this->getType());
        $problem->setData($this->getData());

        return $problem->render();
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): void
    {
        $this->detail = $detail;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode ?? '';
    }

    public function setErrorCode(string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}