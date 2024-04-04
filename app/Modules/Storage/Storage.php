<?php

namespace App\Modules\Storage;

use App\Models\Video;
use App\Models\Media;
use App\Modules\Media\Classes\MimeType;
use App\Modules\Media\Exceptions\MediaNotAllowedException;
use App\Modules\Media\Traits\HandlesMetadata;
use App\Modules\Media\Traits\ValidatesMedia;
use App\Modules\Storage\Exceptions\CannotWriteSecureFileException;
use App\Modules\Upload\UploadInterface;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;

class Storage implements StorageInterface
{
    use ValidatesMedia;
    use HandlesMetadata;

    /**
     * Upload Service
     *
     * @var UploadInterface $uploadService
     */
    protected UploadInterface $uploadService;

    /**
     * The
     */
    protected string $secureDiskRootPath = './storage/app/media/secure';

    public function __construct(UploadInterface $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function findByHash(string $hash): ?Media
    {
        return Media::where('hash', $hash)->first();
    }

    /**
     * Retrieve a secure file's temporary url
     *
     * @param string $path
     *
     * @return string
     */
    public function retrieveSecure(string $path): string
    {
        return StorageFacade::disk('secure')
            ->temporaryUrl(
                $path,
                now()->addSeconds(self::TEMPORARY_URL_TIME_WINDOW)
            );
    }

    /**
     * Store the file
     *
     * @param UploadedFile $file
     *
     * @return Media
     */
    public function store(UploadedFile $file): ?Media
    {
        $photo = new Media();

        $isSuccess = DB::transaction(function () use ($photo, $file) { $fileMetadata = $this->getFileMetadata($file); $path = $this->uploadService->upload($file);

            $photo->hash = hash_file('sha256', $file);
            $photo->format = $fileMetadata['format'];
            $photo->mime_type = $fileMetadata['mimeType'];
            $photo->size = $fileMetadata['size'];
            $photo->path = $path;

            // Create Optimization Job

            return gettype($path) === 'string';
        });

        if ($isSuccess) {
            return $photo;
        } else {
            throw new CannotWriteFileException('File was not saved.');
        }
    }

    /**
     * Store the file
     *
     * @param UploadedFile $file
     * @param Model $model
     * @param string $fileType
     *
     * @return Video
     */
    public function storeVideo(UploadedFile $file, Model $model, string $fileType): ?Video
    {
        $video = new Video();

        $isSuccess = DB::transaction(function () use ($video, $file, $model, $fileType) {
            $fileMetadata = $this->getFileMetadata($file);
            $path = $this->uploadService->upload($file, $model, $fileType);

            $video->hash = hash_file('sha256', $file);
            $video->format = $fileMetadata['format'];
            $video->mime_type = $fileMetadata['mimeType'];
            $video->size = $fileMetadata['size'];
            $video->path = $path;

            return gettype($path) === 'string';
        });

        if ($isSuccess) {
            return $video;
        } else {
            throw new CannotWriteFileException('File was not saved.');
        }
    }

    /**
     * Delete a media set
     *
     * @param Media $photo
     *
     * @return bool
     */
    public function delete(Media $photo): bool
    {
        return StorageFacade::disk('public')->delete($photo->path);
    }

    /**
     * Store the file in a secure container
     *
     * @param UploadedFile $file
     * @param string $name
     * @param string $path
     *
     * @return bool
     */
    public function storeSecure(UploadedFile $file, string $name, string $path): bool
    {
        if (file_exists($this->secureDiskRootPath . $path . $name)) {
            throw new CannotWriteSecureFileException('File already exists');
        }

        try {
            return $file->storeAs($path, $name, 'secure');
        } catch (Exception $e) {
            throw new CannotWriteSecureFileException($e->getMessage());
        }
    }

    /**
     * Delete a secure file
     *
     * @param string $path
     *
     * @return bool
     */
    public function deleteSecure(string $path): bool
    {
        return StorageFacade::disk('secure')->delete($path);
    }

    /**
     * Check the file if its either an image or a video. Throws exception
     * if the file given is neither an image or a video.
     *
     * @param UploadedFile $file
     *
     * @return string
     *
     */
    public function determineFileType(UploadedFile $file): string
    {
        return MimeType::determineFileType($file->getMimeType());
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
