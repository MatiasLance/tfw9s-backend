<?php

namespace App\Http\Controllers;
use App\Models\IndividualRegistration;
use App\Models\TeamRegistration;
use App\Modules\Http\Message;
use Illuminate\Http\Request;
use App\Modules\Storage\StorageInterface;
use Illuminate\Support\Facades\DB;
use App\Models\AgeGroup;

class TransactionController extends Controller
{

    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

     public function __construct(StorageInterface $storageService)
    {
        $this->storageService = $storageService;
    }


    public function retrieve(Message $message, string $key)
    {

        $payload = decrypt($key);

        if ($payload['type'] === 'weekly' || $payload['type'] === 'coast') {
            $data = IndividualRegistration::with('players', 'item')->find($payload['target']);
        }
        else {
            $data = TeamRegistration::with('teams', 'item')->find($payload['target']);
        }

        $message->setContent(200, 'Transaction retrieved', '', [
            'record' => $data
        ]);

        return $message->render();
    }

    public function generate(Request $request, Message $message)
    {
        $type = $request->input('type', null);
        $transaction = $request->input('transaction', null);

        if ($type === 'weekly' || $type === 'coast') {
            $target = IndividualRegistration::where('transaction_id', $transaction)->first();
        } else {
            $target = TeamRegistration::where('transaction_id', $transaction)->first();
        }

        $payload = [];
        $payload['target'] = $target->id;
        $payload['type'] = $type;
        $encryptedToken = encrypt($payload);
        $url = env('APP_URL') . '/transaction/?key=' . $encryptedToken;

        $message->setContent(200, 'Key Generated: ', '', [
            'url' => $url
        ]);

        return $message->render();
    }

    public function saveMedia(Request $request, Message $message)
    {
        $type = $request->input('type');
        $transaction = $request->input('transaction');
        $media = $request->file('photo');

        $result = DB::transaction(function () use ($type, $transaction, $media) {
            if (!$type) {
                return 'Missing `type` parameter.';
            }

            if (!$transaction) {
                return 'Missing `transaction` ID.';
            }

            if (!$media) {
                return 'Missing `photo` file.';
            }

            if ($type === 'weekly' || $type === 'coast') {
                $registration = IndividualRegistration::with('players')
                    ->where('transaction_id', $transaction)
                    ->first();
                $target = $registration?->players?->first();
            } else {
                $registration = TeamRegistration::with('teams')
                    ->where('transaction_id', $transaction)
                    ->first();
                $target = $registration?->teams?->first();
            }

            if (!$target) {
                return 'Target not found from registration.';
            }

            $mediaData = $this->storageService->store($media);

            if (!$mediaData) {
                return 'Failed to store media.';
            }

            // Keep one image only for the target
            foreach ($target->media as $existingMedia) {
                $this->storageService->delete($existingMedia);
                $existingMedia->delete();
            }

            $saved = $target->media()->save($mediaData);


            if (!$saved) {
                return 'Failed to save media to target.';
            }

            return 'success';
        });

        if ($result === 'success') {
            $message->setContent(200, 'Target image uploaded!');
        } else {
            $message->setContent(400, $result);
        }

        return $message->render();
    }
}


