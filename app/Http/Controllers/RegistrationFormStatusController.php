<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistrationFormStatus;

class RegistrationFormStatusController extends Controller
{
    public function retrieve($id)
    {

        $response = RegistrationFormStatus::findOrFail($id);
        dump($response);

        if($response->is_show_count_down_timer){
            return response()->json([
                'success' => true,
                'message' => 'Registration countdown timer is has been turned on.',
                'id' => $response->id
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'The registration countdown timer has been successfully turned off.'
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isShowCountDownTimer' => ['required', 'boolean'],
            'date' => ['required', 'string', 'size:10', 'date_format:Y-m-d'],
            'seriesId' => 'required|exists:series,id'
        ]);

        $response = RegistrationFormStatus::updateOrCreate(
            ['series_id' => $validated['seriesId']],
            [
                'is_show_count_down_timer' => $validated['isShowCountDownTimer'],
                'date' => $validated['date']
            ]
        );
        if($response->is_show_count_down_timer){
            return response()->json([
                'success' => true,
                'message' => 'Registration countdown timer is has been turned on.',
                'id' => $response->id
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'The registration countdown timer has been successfully turned off.'
            ]);
        }
    }
}
