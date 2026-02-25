<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp Message via Damaijaya API
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function sendMessage($phone, $message)
    {
        $appKey = env('WA_DAMAIJAYA_APPKEY');
        $authKey = env('WA_DAMAIJAYA_AUTHKEY');
        $url = env('WA_DAMAIJAYA_URL', 'https://gowa1.damaijaya.my.id/send/message');

        try {
            $response = Http::withBasicAuth($appKey, $authKey)
                ->asForm()
                ->post($url, [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("WA Message sent to $phone");
                return true;
            } else {
                Log::error("Failed to send WA message to $phone. Response: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception in WhatsAppService: " . $e->getMessage());
            return false;
        }
    }
}
