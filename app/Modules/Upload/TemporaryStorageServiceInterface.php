<?php

namespace App\Modules\Upload;

use App\Models\TemporaryFile;
use App\Modules\Upload\Classes\UploadedFile as TemporaryUploadFile;
use Illuminate\Http\UploadedFile;

/**
 * Interface for the Temporary Storage Service
 */
interface TemporaryStorageServiceInterface
{
    /**
     * Store a file temporarily and provide token
     * 
     * @param UploadedFile $file
     * 
     * @return TemporaryFile
     */
    public function store(UploadedFile $file): TemporaryFile;

    /**
     * Retrieve a file stored temporarily
     * 
     * @param string $token
     * 
     * @return App\Modules\Upload\Classes\UploadedFile
     */
    public function retrieve(string $token): TemporaryUploadFile;

    /**
     * Cancel a temporary file's mark for deletion
     * 
     * @param string $token
     * 
     * @return bool;
     */
    public function cancelMarkForDeletion(string $token): bool;

    /**
     * Delete a temporary file and its record in db
     * 
     * @param $token
     * 
     * @return bool;
     */
    public function delete($token): bool;
}