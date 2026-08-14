<?php

namespace App\Services;

use App\Models\TelegramSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function send(string $message): bool
    {
        try {
            $this->sendOrFail($message);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram send failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function sendOrFail(string $message): void
    {
        $setting = TelegramSetting::first();

        if (! $setting || ! $setting->enabled) {
            throw new \RuntimeException('Telegram is not enabled.');
        }

        $token = $setting->bot_token ? decrypt($setting->bot_token) : null;

        if (! $token || ! $setting->chat_id) {
            throw new \RuntimeException('Telegram is not configured.');
        }

        $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $setting->chat_id,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Telegram API error: ' . $response->body());
        }
    }

    public function isEventEnabled(string $event): bool
    {
        $setting = TelegramSetting::first();

        return $setting?->enabled && in_array($event, $setting->events ?? [], true);
    }
}
