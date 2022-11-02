<?php

namespace App\Modules\Media\Traits;

use Illuminate\Http\UploadedFile;

trait HandlesMetadata
{
    /**
     * Get metadata of a file
     * 
     * @param UploadedFile $file
     * 
     * @return array
     */
    protected function getFileMetadata(UploadedFile $file): array
    {
        $metadata = [
            'format' => null,
            'mimeType' => 'applcation/octet-stream',
            'size' => 0,
        ];

        $metadata['format'] = $file->getClientOriginalExtension();
        $metadata['mimeType'] = $this->getFileMimeType($file);
        $metadata['size'] = $file->getSize();

        return $metadata;
    }

    /**
     * Get MimeType of a file
     * 
     * @param UploadedFile $file
     * 
     * @return string
     */
    protected function getFileMimeType(UploadedFile $file): string
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath(), FILEINFO_MIME_TYPE);
    }
}