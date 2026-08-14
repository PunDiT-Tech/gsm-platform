<?php

namespace App\Services;

use App\Models\TelegramSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function send(string $message): bool
    {
        $setting = TelegramSetting::first();

        if (! $setting || ! $setting->enabled) {
            return false;
        }

        $token = $setting->bot_token ? decrypt($setting->bot_token) : null;

        if (! $token || ! $setting->chat_id) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $setting->chat_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->failed()) {
                Log::warning('Telegram send failed', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram send exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function isEventEnabled(string $event): bool
    {
        $setting = TelegramSetting::first();

        return $setting?->enabled && in_array($event, $setting->events ?? [], true);
    }
}
