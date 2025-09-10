<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService{
    public function send($to, $message){
    
        $apiKey = env('SMS_API_KEY');
        $sender = env('SMS_SENDER');

        $response = Http::post('https://api.smsprovider.com/send', [
            'to' => $to,
            'from' => $sender,
            'text' => $message,
            'api_key' => $apiKey,
        ]);

        if ($response->failed()) {
            throw new \Exception('SMS sending failed: ' . $response->body());
        }

        return $response->json();
    }
}
