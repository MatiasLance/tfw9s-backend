<?php

namespace App\Modules\Storage;

use App\Models\Resume;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;

interface StorageInterface
{
    /**
     * Time in seconds the temporary url is available
     *
     * @var int TEMPORARY_URL_TIME_WINDOW
     */
    public const TEMPORARY_URL_TIME_WINDOW = 300;

    /**
     * Find a media by its hash
     *
     * @param string $hash
     *
     * @return Media|null
     */
    public function findByHash(string $hash): ?Media;

    /**
     * Retrieve a secure file's temporary url
     *
     * @param string $path
     *
     * @return string
     */
    public function retrieveSecure(string $path): string;

    /**
     * Store the file
     *
     * @param UploadedFile $file
     * @param Resume $resume
     *
     * @return Media
     */
    public function store(UploadedFile $file): ?Media;

    /**
     * Delete a media set
     *
     * @param Media $mediaSet
     *
     * @return bool
     */
    public function delete(Media $mediaSet): bool;

    /**
     * Store the file in a secure container
     *
     * @param UploadedFile $file
     * @param string $name
     * @param string $path
     *
     * @return bool
     */
    public function storeSecure(UploadedFile $file, string $name, string $path): bool;

    /**
     * Delete a secure file
     *
     * @param string $path
     *
     * @return bool
     */
    public function deleteSecure(string $path): bool;

    /**
     * Store the file
     *
     * @param UploadedFile $file
     * @param Model $model
     * @param string $fileType
     *
     * @return Video
     */
    public function storeVideo(UploadedFile $file, Model $model, string $fileType): ?Video;

    /**
     * Check the file if its either an image or a video. Throws exception
     * if the file given is neither an image or a video.
     *
     * @param UploadedFile $file
     *
     * @return string
     *
     */
    public function determineFileType(UploadedFile $file): string;
}
