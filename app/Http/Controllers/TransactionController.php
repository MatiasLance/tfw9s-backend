<?php

namespace App\Http\Controllers;
use App\Models\IndividualRegistration;
use App\Models\TeamRegistration;
use App\Modules\Http\Message;

use Illuminate\Http\Request;
use App\Models\AgeGroup;

class TransactionController extends Controller
{

    public function retrieve(Message $message, string $key)
    {

        $payload = decrypt($key);

        if ($payload['type'] === 'weekly') {
            $data = IndividualRegistration::with('players')->find($payload['target']);
        }
        else {
            $data = TeamRegistration::with('teams')->find($payload['target']);
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

        if ($type === 'weekly') {
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

}


