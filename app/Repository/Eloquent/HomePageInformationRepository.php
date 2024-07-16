<?php

namespace App\Repository\Eloquent;

use App\Models\HomePageInformation;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\HomePageInformationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Modules\Storage\StorageInterface;

class HomePageInformationRepository extends BaseRepository implements homePageInformationRepositoryInterface
{

    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    public function __construct(HomePageInformation $homePageInfo, StorageInterface $storageService)
    {
        parent::__construct($homePageInfo);
        $this->storageService = $storageService;
    }

    public function retrieveHomePageInfo(int $id): HomePageInformation
    {
        return HomePageInformation::find($id);
    }

    public function updateHomePageInfo(int $id, string $content, ?array $media): bool
    {
        $homePageInfo = $this->find($id);
        $homePageInfo->blurb = $content;

        return DB::transaction(function() use($homePageInfo, $media) {

            if (!is_null($media)) {
                $newMedia = array_filter($media, function ($file) {
                    return $file instanceof UploadedFile;
                });

                $oldMedia = array_filter($media, function ($file) {
                    return !$file instanceof UploadedFile;
                });

                foreach ($homePageInfo->media as $existingMedia) {
                    if (
                        $existingMedia->path !== 'media/default/' . self::PLACEHOLDER_IMAGE &&
                        !in_array($existingMedia->hash, $oldMedia)
                    ) {
                        $this->storageService->delete($existingMedia);
                        $existingMedia->delete();
                    }
                }

                foreach ($newMedia as $newFile) {

                    $Image = $this->storageService->store($newFile);
                    $homePageInfo->media()->save($Image);
                }
            }
            else {
                foreach ($homePageInfo->media as $existingMedia) {
                    $this->storageService->delete($existingMedia);
                    $existingMedia->delete();
                }

            }

            return $homePageInfo->save();
        });
    }

}

