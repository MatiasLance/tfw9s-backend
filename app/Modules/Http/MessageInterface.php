<?php

namespace App\Modules\Http;

use Illuminate\Http\Response;

interface MessageInterface
{
    /**
     * Return a formatted response object
     * 
     * @return Illuminate\Http\Response
     */
    public function render(): Response;

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
    public function setContent(int $status, string $title = '', string $detail = '', array $data = [], bool $overwrite = true): void;
}