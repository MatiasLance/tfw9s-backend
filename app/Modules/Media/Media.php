<?php

namespace App\Modules\Media;

use App\Modules\Media\Classes\MimeType;
use App\Modules\Media\Traits\ValidatesMedia;

/**
 * Media Service
 */
class Media implements MediaInterface
{
    use ValidatesMedia;

    /**
     * Upload the files
     * 
     * @param mixed $files
     * 
     * @return bool
     */
    public function upload($files): bool
    {
        return true;
    }

    /**
     * Checks if the file given is an Image
     * 
     * @param mixed $file
     * 
     * @return bool
     */
    protected function isImage($file): bool
    {
        return $this->validateMedia($file, MimeType::VALIDATE_IMAGE);
    }

    /**
     * Checks if the files given is a Video
     * 
     * @param mixed $file
     * 
     * @return bool
     */
    protected function isVideo($file): bool
    {
        return $this->validateMedia($file, MimeType::VALIDATE_VIDEO);
    }
}