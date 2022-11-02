<?php

namespace App\Modules\Upload;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;

/**
 * LocalDisk class uploads the media to the server's local
 * disk storage
 */
class LocalDisk implements UploadInterface
{
    /**
     * Path where to store the files
     * 
     * @var string $path
     */
    protected string $path = 'media/items';

    /**
     * Upload media. Returns the path to the resource
     * 
     * @param UploadedFile $media
     * 
     * @return string
     */
    public function upload(UploadedFile $media): string
    {
        $filename = $this->generateUniqueMediaName($media);
        return $media->storePubliclyAs($this->path, $filename, 'public');
    }

    /**
     * Retrieve media
     * 
     * @todo Add code
     */
    public function retrieve()
    {
        return '';
    }

    /**
     * Generate a unique name for storing a file to ensure it
     * doesn't overwrite an existing file.
     * 
     * @param UploadedFile  $file
     * @param int $attempts (Optional) By default set to 3. The number of times the
     *                      function repeats incase the generated name is taken.
     * 
     * @return string
     */
    protected function generateUniqueMediaName(UploadedFile $file, int $attempts = 3): string
    {
        $uniqueName = null;
        $extension = $this->getFileExtension($file);
        for ($turns=0; $turns < $attempts; $turns++) {
            $name = hash('sha256', microtime(true) . $file->getFilename()) . $extension;
            $isExisting = file_exists(storage_path($this->path));
            if (! $isExisting) {
                $uniqueName = $name;
                break;
            }
        }

        if (! is_null($uniqueName)) {
            return $uniqueName;
        } else {
            throw new CannotWriteFileException('Failed to generate a unique name for media');
        }
    }

    /**
     * Retrieve the file's extension. Returns an empty string if no extension is found.
     * 
     * The function's purpose is to automatically add a '.' before an extension, if it
     * is present, so that it is easier to concatenate to the filename
     * 
     * @param UploadedFile $file
     * 
     * @return string
     */
    protected function getFileExtension(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        if (! is_null($extension) || $extension !== '') {
            return '.' . $extension;
        } else {
            return '';
        }
    }
}