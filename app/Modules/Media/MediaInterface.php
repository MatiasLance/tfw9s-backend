<?php

namespace App\Modules\Media;

/**
 * Interface for the media service
 * 
 * The media service is in charge of the validation and optimization
 * of the media before uploading it to the storage of choice.
 */
interface MediaInterface
{
    /**
     * Upload the files
     * 
     * @param array $files
     * 
     * @return bool
     */
    public function upload(array $files): bool;

    // public function retrieve();

    // public function validate();

}