<?php

namespace App\Modules\Upload;

use App\Models\TemporaryFile;
use App\Modules\Media\Classes\MimeType;
use App\Modules\Media\Traits\ValidatesMedia;
use App\Modules\Upload\Classes\UploadedFile as TemporaryUploadedFile;
use App\Modules\Upload\Exceptions\FileTypeNotAllowedException;
use App\Repository\TemporaryFileRepositoryInterface;
use Illuminate\Http\UploadedFile;

class TemporaryStorageService implements TemporaryStorageServiceInterface
{
    use ValidatesMedia;

    protected TemporaryFileRepositoryInterface $temporaryFileRepository;

    public function __construct(TemporaryFileRepositoryInterface $temporaryFileRepository)
    {
        $this->temporaryFileRepository = $temporaryFileRepository;
    }

    /**
     * Store a file temporarily and provide token
     * 
     * @param UploadedFile $file
     * 
     * @return TemporaryFile
     */
    public function store(UploadedFile $file): TemporaryFile
    {
        if (! $this->validateMedia($file, MimeType::VALIDATE_ALL)){
            throw new FileTypeNotAllowedException('File type of the file is not allowed');
        }

        return $this->temporaryFileRepository->store($file);
    }

    /**
     * Retrieve a file stored temporarily
     * 
     * @param string $token
     * 
     * @return App\Modules\Upload\Classes\UploadedFile
     */
    public function retrieve(string $token): TemporaryUploadedFile
    {
        return $this->temporaryFileRepository->retrieve($token);
    }

    /**
     * Cancel a temporary file's mark for deletion
     * 
     * @param string $token
     * 
     * @return bool
     */
    public function cancelMarkForDeletion(string $token): bool
    {
        return $this->temporaryFileRepository->cancelMarkForDeletion($token);
    }

    /**
     * Delete a temporary file and its record in db
     * 
     * @param $token
     * 
     * @return bool;
     */
    public function delete($token): bool
    {
        return $this->temporaryFileRepository->delete($token);
    }
}