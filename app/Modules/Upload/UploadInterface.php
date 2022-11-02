<?php

namespace App\Modules\Upload;

use Illuminate\Http\UploadedFile;

/**
 * Interface for the upload service
 * 
 * The upload service is in charge of uploading the media files in the
 * storage of choice. Uploading and retrieval of the media is the sole
 * purpose of the upload service.
 */
interface UploadInterface
{
    /**
     * Upload media. Returns the path to the resource
     * 
     * @param UploadedFile $media
     * 
     * @return string
     */
    public function upload(UploadedFile $media): string;

    /**
     * Retrieve media
     */
    public function retrieve();
}