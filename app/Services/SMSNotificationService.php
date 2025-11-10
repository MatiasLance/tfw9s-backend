<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Twilio\Http\CurlClient;
use App\Models\Team;
use App\Models\Series;

class SMSNotificationService
{
    public function sendLink(Team $team, Series $series)
    {
        try {
            $twilio = new Client(
                env('TWILIO_SID'),
                env('TWILIO_AUTH_TOKEN')
            );

            $from = env('TWILIO_PHONE_NUMBER');

            $payload = ['series' => $series->id, 'team' => $team->id];
            $encryptedToken = encrypt($payload);

            $link = url('/register?id=' . $series->id . '&series=' . urlencode($series->name) . '&price=' . $series->price . '&token=' . $encryptedToken);

            $textMessage = "Hello! Join your team for {$series->name} using this link: {$link}";

            $phoneNumbers = array_filter([
            $this->formatPhoneNumber($team->coach_mobile),
            $this->formatPhoneNumber($team->manager_mobile)
            ]);

            $results = [];
            $errors = [];
            
            foreach ($phoneNumbers as $number) {
                if (!$this->isValidPhoneNumber($number)) {
                    $errors[$number] = 'Invalid phone number format';
                    continue;
                }

                try {
                    Log::info("Attempting to send SMS to: {$number}");
                    
                    $message = $twilio->messages->create(
                        $number,
                        [
                            'from' => $from,
                            'body' => $textMessage,
                        ]
                    );
                    
                    $results[$number] = [
                        'sid' => $message->sid,
                        'status' => $message->status
                    ];
                    
                    Log::info("SMS sent successfully to {$number}, SID: {$message->sid}");
                    
                } catch (TwilioException $e) {
                    $errorMsg = $e->getMessage();
                    $errors[$number] = $errorMsg;
                    Log::error("Twilio error for {$number}: {$errorMsg}");
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    $errors[$number] = $errorMsg;
                    Log::error("General error for {$number}: {$errorMsg}");
                }
                
                usleep(500000);
            }

            return [
                'message' => 'Invitation links processed',
                'successful' => $results,
                'errors' => $errors
            ];

        } catch(\Exception $e) {
            Log::error('SMS Service Error: ' . $e->getMessage());
            
            return [
                'error' => 'Failed to send invitations: ' . $e->getMessage()
            ];
        }
    }

    public function sendToAll(Collection $teams, Series $series)
    {
        try {
            $twilio = new Client(
                env('TWILIO_SID'),
                env('TWILIO_AUTH_TOKEN')
            );

            $from = env('TWILIO_PHONE_NUMBER');

            foreach($teams as $team) {
                $payload = ['series' => $series->id, 'team' => $team->id];
                $encryptedToken = encrypt($payload);

                $link = url('/register?id=' . $series->id . '&series=' . urlencode($series->name) . '&price=' . $series->price . '&token=' . $encryptedToken);

                $textMessage = "Hello! Join your team for {$series->name} using this link: {$link}";

                $phoneNumbers = array_filter([
                $this->formatPhoneNumber($team->coach_mobile),
                $this->formatPhoneNumber($team->manager_mobile)
                ]);

                $results = [];
                $errors = [];
                
                foreach ($phoneNumbers as $number) {
                    if (!$this->isValidPhoneNumber($number)) {
                        $errors[$number] = 'Invalid phone number format';
                        continue;
                    }

                    try {
                        Log::info("Attempting to send SMS to: {$number}");
                        
                        $message = $twilio->messages->create(
                            $number,
                            [
                                'from' => $from,
                                'body' => $textMessage,
                            ]
                        );
                        
                        $results[$number] = [
                            'sid' => $message->sid,
                            'status' => $message->status
                        ];
                        
                        Log::info("SMS sent successfully to {$number}, SID: {$message->sid}");
                        
                    } catch (TwilioException $e) {
                        $errorMsg = $e->getMessage();
                        $errors[$number] = $errorMsg;
                        Log::error("Twilio error for {$number}: {$errorMsg}");
                    } catch (\Exception $e) {
                        $errorMsg = $e->getMessage();
                        $errors[$number] = $errorMsg;
                        Log::error("General error for {$number}: {$errorMsg}");
                    }
                    
                    usleep(500000);
                }

                return [
                    'message' => 'Invitation links processed',
                    'successful' => $results,
                    'errors' => $errors
                ];
            }

        } catch(\Exception $e) {
            Log::error('SMS Service Error: ' . $e->getMessage());
            
            return [
                'error' => 'Failed to send invitations: ' . $e->getMessage()
            ];
        }
    }

    protected function isValidPhoneNumber($number): bool
    {
        return !empty($number) && preg_match('/^\+?[1-9]\d{1,14}$/', $number);
    }
    
    protected function formatPhoneNumber($number): string
    {
        $cleaned = preg_replace('/[^\d+]/', '', $number);
        
        if (!str_starts_with($cleaned, '+')) {
            if (str_starts_with($cleaned, '0')) {
                $cleaned = substr($cleaned, 1);
            }
            $cleaned = '+61' . $cleaned;
        }
        
        return $cleaned;
    }
    
    public function testTwilioConnection()
    {
        try {
            $twilio = new Client(
                env('TWILIO_SID'),
                env('TWILIO_AUTH_TOKEN')
            );
            
            $account = $twilio->api->v2010->accounts(env('TWILIO_SID'))->fetch();
            
            return response()->json([
                'status' => 'success',
                'account' => [
                    'friendly_name' => $account->friendlyName,
                    'status' => $account->status,
                ]
            ]);
            
        } catch (TwilioException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function testNetworkConnectivity()
    {
        try {
            $start = microtime(true);
            
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);
            
            $response = $client->get('https://api.twilio.com');
            
            $pingTime = round((microtime(true) - $start) * 1000, 2);
            
            return response()->json([
                'status' => 'success',
                'ping_ms' => $pingTime,
                'twilio_status' => $response->getStatusCode()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'ping_ms' => round((microtime(true) - $start) * 1000, 2)
            ], 500);
        }
    }
}