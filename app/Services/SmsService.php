<?php

namespace App\Services;

use App\Models\SmsApi;
use Illuminate\Support\Facades\Http;

class SmsService
{
    public static function sendSms($numbers, $message)
    {
        $api = SmsApi::first();

        $url = 'http://bulksmsbd.net/api/smsapi';
        $number = $numbers;
        $message = $message;

        $response = Http::asForm()->post($url, [
            'api_key' => $api->api_key,
            'senderid' => $api->sender_id,
            'number' => $number,
            'message' => $message,
        ]);

        return $response->body();
    }
}
