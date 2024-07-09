<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Http\Message;
use App\Modules\HomePageInformation\HomePageInformationServiceInterface;
use App\Modules\Storage\StorageInterface;

class HomePageInformationController extends Controller
{

    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    protected HomePageInformationServiceInterface $homePageInformationService;

    public function __construct(HomePageInformationServiceInterface $homePageInformationService, StorageInterface $storageService)
    {
        $this->homePageInformationService = $homePageInformationService;
        $this->storageService = $storageService;
    }

    public function retrieve(Message $message, int $id)
    {
        $teamFolder = $this->homePageInformationService->retrieveHomePageInfo($id);

        $message->setContent(200, 'TeamFolder retrieved', '', [
            'teamFolder' => $teamFolder
        ]);

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $content = $request->input('content');

        $newPhoto = $request->file('photo') ?? [];
        $existingPhoto = $request->input('photo') ?? [];
        $newPhotoCount = count($newPhoto);
        $existingPhotoCount = count($existingPhoto);

        if (
            $request->has('photo') &&
            (
                $newPhotoCount > 0 ||
                $existingPhotoCount > 0
            )
        ) {
            foreach ($existingPhoto as $existingPhotoHash) {
                array_push($newPhoto, $existingPhotoHash);
            }
            $media = $newPhoto;
        } else {
            $media = null;
        }

        $isSuccess = $this->homePageInformationService->updateHomePageInfo($id, $content, $media);

        if ($isSuccess) {
            $message->setContent(200, 'Team updated');
        } else {
            $message->setContent(400, 'Team not updated');
        }

        return $message->render();
    }

}

