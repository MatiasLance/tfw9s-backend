<?php

namespace App\Http\Controllers;

use App\Models\RegistrationFormStatus;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Carbon\Carbon;
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
            $series = Series::with('registrationFormStatus')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Series not found.'
            ], 404);
        }

        $timer = $series->registrationFormStatus;

        if (!$timer) {
            return response()->json([
                'success' => true,
                'message' => 'No configuration found. Using defaults.',
                'data' => [
                    'isShowCountDownTimer' => false,
                    'timerMode' => 'duration',
                    'countdownUnit' => 'days',
                    'countdownValue' => 1,
                    'date' => null,
                    'seriesId' => $id
                ]
            ]);
        }

        $isEnabled = (bool) $timer->is_show_count_down_timer;
        
        // $formattedDate = $timer->opens_at ? $timer->opens_at->format('Y-m-d\TH:i') : null;

        $responseData = [
            'success' => true,
            'message' => $isEnabled 
                ? 'Countdown timer is currently enabled.' 
                : 'Countdown timer is currently disabled.',
            'data' => [
                'seriesId' => $series->id,
                'isShowCountDownTimer' => $isEnabled,
                'timerMode' => $timer->timer_mode ?? ($timer->opens_at ? 'date' : 'duration'),
                'countdownUnit' => $timer->countdown_unit ?? 'days',
                'countdownValue' => $timer->countdown_value ?? 1,
                'date' => $timer->opens_at,
            ]
        ];

        $this->notifyService->sendNotification($responseData);
        
        return response()->json($responseData);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'seriesId' => 'required|exists:series,id',
            'isShowCountDownTimer' => 'required|boolean',
            'timerMode' => 'required_if:isShowCountDownTimer,true|in:duration,date',
            'date' => 'required_if:timerMode,date|nullable|date',
            'countdownValue' => 'required_if:timerMode,duration|nullable|integer|min:1',
            'countdownUnit' => 'required_if:timerMode,duration|nullable|in:days,hours,minutes',
        ]);

        $series = Series::findOrFail($validated['seriesId']);

        if ($series->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot enable timer for a paused series.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $opensAt = null;

            if ($validated['isShowCountDownTimer']) {
                if ($validated['timerMode'] === 'date') {
                    $opensAt = Carbon::parse($validated['date']);
                } else {
                    $unit = $validated['countdownUnit'];
                    $value = $validated['countdownValue'];
                    
                    $opensAt = Carbon::now()->add($unit, $value);
                }
            }

            $config = RegistrationFormStatus::updateOrCreate(
                ['series_id' => $validated['seriesId']],
                [
                    'is_show_count_down_timer' => $validated['isShowCountDownTimer'],
                    'opens_at' => $opensAt,
                    'timer_mode' => $validated['timerMode'] ?? 'date',
                    'countdown_unit' => $validated['countdownUnit'],
                    'countdown_value' => $validated['countdownValue'],
                ]
            );

            DB::commit();

            $status = $config->is_show_count_down_timer ? 'enabled' : 'disabled';
            
            return response()->json([
                'success' => true,
                'message' => "Registration countdown timer has been successfully {$status}.",
                'data' => [
                    'series_id' => $config->series_id
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Timer Update Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to update timer configuration.'
            ], 500);
        }
    }
}
