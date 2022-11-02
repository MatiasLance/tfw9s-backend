<?php

namespace App\Modules\Media\Traits;

use App\Modules\Media\Classes\MimeType;
use App\Modules\Media\Exceptions\InvalidValidateOptionException;

/**
 * Trait ValidatesMedia
 * 
 * @see UserRepository:295 For getting mime type of file
 * 
 * Provides methods to validate media
 */
trait ValidatesMedia
{
    /**
     * Allowed Media MimeTypes
     * 
     * @var string[] $allowedMedia
     */
    protected $allowedMedia = [
        /**
         * Image
         */
        MimeType::JPEG,
        MimeType::JPG,
        MimeType::PNG,
        MimeType::WEBP,
    ];

    /**
     * Allowed Image MimeTypes
     * 
     * @var string[] $allowedImages
     */
    protected $allowedImages = [
        MimeType::JPEG,
        MimeType::JPG,
        MimeType::PNG,
        MimeType::WEBP,
    ];

    /**
     * Allowed Video MimeTypes
     * 
     * @var string[] $allowedVideos
     */
    protected $allowedVideos = [];

    /**
     * Validate the media if it is allowed.
     * 
     * @param string $filename
     * @param int $option Check validate option constants
     * 
     * @return bool
     */
    public function validateMedia(string $filename, int $option = MimeType::VALIDATE_ALL): bool
    {
        $mimeType = $this->getMimeType($filename);

        switch ($option) {
            case MimeType::VALIDATE_ALL:
                return in_array($mimeType, $this->allowedMedia);
                break;

            case MimeType::VALIDATE_IMAGE:
                return in_array($mimeType, $this->allowedImages);
                break;

            case MimeType::VALIDATE_VIDEO:
                return in_array($mimeType, $this->allowedVideos);
                break;
            
            default:
                throw new InvalidValidateOptionException('Option given is invalid.');
                break;
        }
    }

    /**
     * Retrieve the mime type of a file
     * 
     * @param string $filename
     * 
     * @return string
     */
    private function getMimeType(string $filename): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($finfo, $filename);
    }

    /**
     * Retrieve allowed media
     * 
     * @return array
     */
    public function getAllowedMedia(): array
    {
        return $this->allowedMedia;
    }

    /**
     * Set allowed Media
     * 
     * @param string[] $allowedMedia
     * 
     * @return $this
     */
    public function setAllowedMedia(array $allowedMedia)
    {
        $this->allowedMedia = $allowedMedia;

        return $this;
    }

    /**
     * Allow Media
     * 
     * @param string $mimeTypes,... Mimetypes you want to be allowed.
     * 
     * @return $this
     */

    public function allowMedia(string ...$mimeTypes)
    {
       $this->allowedMedia = array_merge($this->allowedMedia, $mimeTypes);

       return $this;
    }

    /**
     * Disallow Media
     * 
     * @param string $mimeTypes,... Mimetypes you want to disallow
     * 
     * @return $this
     */
    public function disallowMedia(string ...$mimeTypes)
    {
        foreach($this->allowedMedia as $index => $allowedMedia){
            if (in_array($allowedMedia, $mimeTypes)) {
                array_splice($this->allowedMedia, $index, 1);
            }
        }

        return $this;
    }

    /**
     * Retrieve allowed images
     * 
     * @return array
     */
    public function getAllowedImages(): array
    {
        return $this->allowedImages;
    }

    /**
     * Set allowed imagessvg
     * 
     * @param string[] $allowedImages
     * 
     * @return $this
     */
    public function setAllowedImages(array $allowedImages)
    {
        $this->allowedImages = $allowedImages;

        return $this;
    }

    /**
     * Allow mimetypes for images
     * 
     * @param string $mimeTypes,... Mimetypes you want to allow for images
     * 
     * @return $this
     */
    public function allowImages(string ...$mimeTypes)
    {
        $this->allowedImages = array_merge($this->allowedImages, $mimeTypes);

        return $this;
    }

    /**
     * Disallow mimetypes for videos
     * 
     * @param string $mimeTypes,... Mimetypes you want to disallow
     * 
     * @return $this
     */
    public function disallowImages(string ...$mimeTypes)
    {
        foreach($this->allowedImages as $index => $allowedImage){
            if (in_array($allowedImage, $mimeTypes)) {
                array_splice($this->allowedImages, $index, 1);
            }
        }

        return $this;
    }

    /**
     * Retrieve allowed videos
     * 
     * @return array
     */
    public function getAllowedVideos(): array
    {
        return $this->allowedVideos;
    }

    /**
     * Set allowed videos
     * 
     * @param string[] $allowedVideos
     * 
     * @return $this
     */
    public function setAllowedVideos(array $allowedVideos)
    {
        $this->allowedVideos = $allowedVideos;

        return $this;
    }

    /**
     * Allow mimetypes for videos
     * 
     * @param string $mimeTypes,...Mimetypes you want to allow for videos
     * 
     * @return $this
     */
    public function allowVideos(string ...$mimeTypes)
    {
        $this->allowedVideos = array_merge($this->allowedVideos, $mimeTypes);

        return $this;
    }

    /**
     * Disallow mimetypes for videos
     * 
     * @param string $mimeTypes,...Mimetypes you want to disallow
     * 
     * @return $this
     */
    public function disallowVideos(string ...$mimeTypes)
    {
        foreach($this->allowedVideos as $index => $allowedVideo){
            if (in_array($allowedVideo, $mimeTypes)) {
                array_splice($this->allowedVideos, $index, 1);
            }
        }

        return $this;
    }
}