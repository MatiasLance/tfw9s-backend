<?php

namespace App\Http\Controllers;

use App\Models\RegistrationFormStatus;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use ModelNotFoundException;
use App\Services\NotifyService;

class RegistrationFormStatusController extends Controller
{

    protected $notifyService;

    public function __construct(NotifyService $notifyService)
    {
        $this->notifyService = $notifyService;
    }

    public function retrieve(int $id)
    {

        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid series ID provided.',
            ]);
        }

        try {
            $series = Series::findOrFail($id);
        } catch (ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Series not found.'
            ]);
        }

         $timer = $series->registrationFormStatus;
    
        if (!$timer) {
            return response()->json([
                'success' => false,
                'message' => 'No timer configuration found for this series.',
                'data' => [
                    'is_show_count_down_timer' => false,
                    'date' => null
                ]
            ]);
        }

        $isEnabled = $timer->is_show_count_down_timer;

        $data = [
            'success' => true,
            'message' => $isEnabled 
                ? 'Countdown timer is currently enabled.' 
                : 'Countdown timer is currently disabled.',
            'data' => [
                'is_show_count_down_timer' => $isEnabled,
                'date' => $timer->date ? $timer->date->format('Y-m-d') : null,
            ]
        ];

        $this->notifyService->sendNotification($data);
        
        return response()->json([
            'success' => true,
            'message' => $isEnabled 
                ? 'Countdown timer is currently enabled.' 
                : 'Countdown timer is currently disabled.',
            'data' => [
                'is_show_count_down_timer' => $isEnabled,
                'date' => $timer->date ? $timer->date->format('Y-m-d') : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        // https://stackoverflow.com/questions/50287823/validating-a-custom-date-format-in-with-laravel-validator
        $validated = $request->validate([
            'isShowCountDownTimer' => ['required', 'boolean'],
            'date' => 'required|date_format:Y-m-d',
            'seriesId' => 'required|exists:series,id'
        ]);

        $series = Series::findOrFail($validated['seriesId']);

        if($series->is_paused){
            return response()->json([
                'success' => false,
                'message' => 'Cannot enable timer for a paused series.'
            ]);
        }

        try {
            DB::beginTransaction();

            $response = RegistrationFormStatus::updateOrCreate(
                ['series_id' => $validated['seriesId']],
                [
                    'is_show_count_down_timer' => $validated['isShowCountDownTimer'],
                    'date' => $validated['date']
                ]
            );

            DB::commit();

            if($response->is_show_count_down_timer){
                return response()->json([
                    'success' => true,
                    'message' => 'Registration countdown timer is has been turned on.',
                    'id' => $response->series_id
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'The registration countdown timer has been successfully turned off.',
                    'id' => $response->series_id
                ]);
            }

        }catch(Throwable $e){
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to update timer configuration. Please try again.'
            ]);
        }
    }
}
