<?php

namespace App\Modules\Upload\Classes;

use App\Modules\Upload\TemporaryStorageService;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\App;

class UploadedFile extends HttpUploadedFile
{
    /**
     * Token as temporary file
     */
    protected string $token;

    public function __construct(string $path, string $originalName, string $mimeType = null, int $error = null, bool $test = false, string $token = null)
    {
        parent::__construct($path, $originalName, $mimeType, $error, $test);
        if (! is_null($token)) {
            $this->setToken($token);
        }
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Cancel this temporary file's deletion
     * 
     * @return bool
     */
    public function cancelMarkForDeletion(): bool
    {
        $temporaryStorageService = App::make(TemporaryStorageService::class);
        return $temporaryStorageService->cancelMarkForDeletion($this->getToken());
    }
}