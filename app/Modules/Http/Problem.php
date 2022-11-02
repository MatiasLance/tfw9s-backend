<?php

namespace App\Modules\Http;

use App\Modules\Http\Exceptions\NoErrorCodeException;
use Illuminate\Http\Response;

/**
 * Define the detail an error or exception in consistent
 * format conforming with RFC7807
 * 
 * @see https://datatracker.ietf.org/doc/html/rfc7807
 */
class Problem extends Message
{
    /**
     * URI reference to identify the problem type.
     * 
     * @var string $type
     */
    protected string $type = "about:blank";

    /**
     * Unique problem indentifier
     * 
     * @var string $errorCode
     */
    protected string $errorCode;

    public function __construct($status = 400, string $errorCode = '', string $type = 'about:blank', string $title = '', string $detail = '', array $data = [])
    {
        parent::__construct($status, $title, $detail, $data);
        $this->setErrorCode($errorCode);
        $this->setType($type);
    }

    public function render(): Response
    {
        $status = $this->getStatus();
        $type = $this->getType();
        $title = $this->getTitle();
        $detail = $this->getDetail();
        $data = $this->getData();
        $errorCode = $this->getErrorCode();

        $responseObject = (object)[
            'status' => $status,
            'type' => $type,
        ];

        if (!$this->isEmpty($title)){
            $responseObject->title = $title;
        }

        if (!$this->isEmpty($detail)){
            $responseObject->detail = $detail;
        }

        if (!$this->isEmpty($data)){
            $responseObject->data = $data;
        }

        if (!$this->isEmpty($errorCode)){
            $responseObject->errorCode = $errorCode;
        } else {
            throw new NoErrorCodeException("No error code assigned on this exception");
        }

        return response(json_encode($responseObject), $status);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
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