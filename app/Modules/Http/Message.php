<?php

namespace App\Modules\Http;

use Illuminate\Http\Response;

/**
 * Define a consistent response format for API consumers.
 * Intended to work with RFC7807.
 * 
 * @see https://datatracker.ietf.org/doc/html/rfc7807
 */
class Message implements MessageInterface
{
    /**
     * The HTTP status code
     * 
     * This is added in case proxies change the HTTP code
     * 
     * @var int $status
     */
    protected int $status;

    /**
     * A short summary of the response
     * 
     * @var string $title
     */
    protected string $title;

    /**
     * Description of the response
     * 
     * @var string $detail
     */
    protected string $detail;

    /**
     * Any additional data to be sent along with the response
     * 
     * @var array $data
     */
    protected ?array $data;

    public function __construct($status = 200, string $title = '', string $detail = '', array $data = [])
    {
        $this->setContent($status, $title, $detail, $data, true);
    }

    /**
     * Return a formatted response object
     * 
     * @return Illuminate\Http\Response
     */
    public function render(): Response
    {
        $status = $this->getStatus();
        $title = $this->getTitle();
        $detail = $this->getDetail();
        $data = $this->getData();

        $responseObject = (object)[
            'status' => $status,
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

        return response(json_encode($responseObject), $status);
    }

    /**
     * Set the response content
     * 
     * @param int $status
     * @param string $title
     * @param string $detail
     * @param array $data
     * @param bool $overwrite When true, will overwrite existing data value.
     *                      When false, merges the passed data with existing.
     *                      By default set to true
     * 
     * @return void
     */
    public function setContent(int $status, string $title = '', string $detail = '', array $data = [], bool $overwrite = true): void
    {
        $this->setStatus($status);
        $this->setTitle($title);
        $this->setDetail($detail);

        if (!$overwrite){
            $data = array_merge($this->getData(), $data);
        }
        
        $this->setData($data);
    }

    protected function isEmpty($value): bool
    {
        $type = gettype($value);

        if ($type === 'string') {
            return is_null($value) || !isset($value) || $value === '';
        } else if ($type === 'array') {
            return is_null($value) || !isset($value) || count($value) < 1;
        } else {
            return is_null($value) || !isset($value);
        }
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

    public function addDataAttribute(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function successMessage() {
        return response()->json([
            "message" => "Data save successfully",
            "status" => 200
        ]);
    }
    public function errorMessage() {
        return response()->json([
            "message" => "Data failed to save",
            "status" => 400
        ]);
    }

    public function updateSuccessMessage() {
        return response()->json([
            "Message" => "Data successfully updated",
            "Status" => 200
        ]);
    }
    public function updateErrorMessage() {
        return response()->json([
            "Message" => "Data failed to update",
            "Status" => 400
        ]);
    }
    public function deleteSuccessMessage() {
        return response()->json([
            "Message" => "Data successfully deleted",
            "Status" => 200
        ]);
    }
    public function deleteErrorMessage() {
        return response()->json([
            "Message" => "Failed to delete data",
            "Status" => 400
        ]);
    }
}