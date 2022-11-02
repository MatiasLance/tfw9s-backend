<?php

namespace App\Modules\Media\Classes;

/**
 * Class MimeType
 * 
 * Provides enums for common MIME Types
 * 
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 */
class MimeType
{
    /**
     * Images
     */

    public const JPEG = 'image/jpeg';
    public const JPG = 'image/jpeg';
    public const PNG = 'image/png';
    public const GIF = 'image/gif';
    public const WEBP = 'image/webp';
    public const HEIC = 'image/heic';
    public const SVG = 'image/svg+xml';

    /**
     * Videos
     */

     public const MP4 = 'video/mp4';
     public const MPEG = 'video/mpeg';
     public const WEBM = 'video/webm';

     /**
      * Validate Options
      */

     public const VALIDATE_ALL = 0;
     public const VALIDATE_IMAGE = 1;
     public const VALIDATE_VIDEO = 2;
}