<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\PartnerSponsor\PartnerSponsorServiceInterface;
use Illuminate\Http\Request;
use App\Models\PartnerSponsor;

class PartnerSponsorController extends Controller
{
    protected PartnerSponsorServiceInterface $partnerSponsorService;

    public function __construct(PartnerSponsorServiceInterface $partnerSponsorService)
    {
        $this->partnerSponsorService = $partnerSponsorService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxPartnerSponsorsPerPage = $request->query('maxPartnerSponsorsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_partnerSponsor_per_page' => $maxPartnerSponsorsPerPage,
        ];

        $partnerSponsors = $this->partnerSponsorService->listPartnerSponsors($filter);

        $message->setContent(200, 'PartnerSponsors retrieved', '', $partnerSponsors->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $partnerSponsor = $this->partnerSponsorService->retrievePartnerSponsor($id);

        $message->setContent(200, 'PartnerSponsor retrieved', '', [
            'partnerSponsor' => $partnerSponsor
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $company_name = $request->input('company_name');
        $hyperlink = $request->input('hyperlink');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $description = $request->input('description') ?? '';
        $media = $request->file('photo') ?? [];

        $partnerSponsor = $this->partnerSponsorService->createPartnerSponsor($company_name, $hyperlink, $first_name, $last_name, $description, $media);

        if ($partnerSponsor instanceof PartnerSponsor) {
            $message->setContent(201, 'PartnerSponsor created', '', [
                'partnerSponsor' => $partnerSponsor
            ]);
        } else {
            $message->setContent(400, 'PartnerSponsor not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $company_name = $request->input('company_name');
        $hyperlink = $request->input('hyperlink');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $description = $request->input('description') ?? '';

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

        $isSuccess = $this->partnerSponsorService->updatePartnerSponsor($id, $company_name, $hyperlink, $first_name, $last_name, $description, $media);

        if ($isSuccess) {
            $message->setContent(200, 'PartnerSponsor updated');
        } else {
            $message->setContent(400, 'PartnerSponsor not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $partnerSponsor = $this->partnerSponsorService->retrievePartnerSponsor($id);

        $isSuccess = $this->partnerSponsorService->deletePartnerSponsor($user, $partnerSponsor);

        if ($isSuccess) {
            $message->setContent(200, 'PartnerSponsor deleted');
        } else {
            $message->setContent(400, 'PartnerSponsor not updated');
        }

        return $message->render();
    }
}



