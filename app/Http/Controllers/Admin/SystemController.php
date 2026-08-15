<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function index(): View
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'cache' => $this->checkCache(),
            'scheduler' => $this->checkScheduler(),
            'telegram' => $this->checkTelegram(),
            'mail' => $this->checkMail(),
            'app_debug' => $this->checkAppDebug(),
            'secure_cookie' => $this->checkSecureCookie(),
        ];

        $diskUsage = $this->diskUsage();

        return view('admin.system.index', compact('checks', 'diskUsage'));
    }

    protected function checkDatabase(): array
    {
        try {
            DB::select('select 1');
            return ['status' => 'CONNECTED'];
        } catch (\Throwable $e) {
            return ['status' => 'FAILED'];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $bytes = Storage::disk('local')->size('.');

            return ['status' => 'CONNECTED'];
        } catch (\Throwable $e) {
            return ['status' => 'FAILED'];
        }
    }

    protected function checkQueue(): array
    {
        try {
            Queue::connection()->size('default');

            return ['status' => 'CONNECTED'];
        } catch (\Throwable $e) {
            return ['status' => 'FAILED'];
        }
    }

    protected function checkCache(): array
    {
        try {
            Cache::put('health-check', true, 1);

            return Cache::get('health-check') ? ['status' => 'CONNECTED'] : ['status' => 'WARNING'];
        } catch (\Throwable $e) {
            return ['status' => 'FAILED'];
        }
    }

    protected function checkScheduler(): array
    {
        $lastRun = Cache::get('scheduler-last-run');

        if (! $lastRun) {
            return ['status' => 'WARNING', 'note' => 'Scheduler heartbeat not seen yet.'];
        }

        return now()->diffInMinutes($lastRun) <= 10
            ? ['status' => 'CONNECTED', 'note' => 'Last run ' . $lastRun->diffForHumans()]
            : ['status' => 'WARNING', 'note' => 'Last run ' . $lastRun->diffForHumans()];
    }

    protected function checkTelegram(): array
    {
        $setting = TelegramSetting::first();

        if (! $setting?->enabled) {
            return ['status' => 'WARNING', 'note' => 'Telegram is disabled.'];
        }

        $token = $setting->bot_token ? \Illuminate\Support\Facades\Crypt::decryptString($setting->bot_token) : null;

        if (! $token || ! $setting->chat_id) {
            return ['status' => 'FAILED', 'note' => 'Token or chat id missing.'];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("https://api.telegram.org/bot{$token}/getMe");

            if ($response->successful() && $response->json('ok') === true) {
                return ['status' => 'CONNECTED', 'note' => 'Bot "' . ($response->json('result.username') ?? '?') . '" reachable.'];
            }

            return ['status' => 'FAILED', 'note' => 'Telegram API rejected the token.'];
        } catch (\Throwable $e) {
            return ['status' => 'FAILED', 'note' => 'Network error: ' . $e->getMessage()];
        }
    }

    protected function checkMail(): array
    {
        $driver = config('mail.default');

        if ($driver === 'log' || $driver === 'array') {
            return ['status' => 'WARNING', 'note' => "Driver '{$driver}' is not a real delivery channel."];
        }

        try {
            $manager = app('mail.manager');
            $mailer = $manager->mailer($driver);
            $transport = $mailer->getSymfonyTransport();

            $transport->start();

            return ['status' => 'CONNECTED', 'note' => 'Mail transport connected (' . $driver . ').'];
        } catch (\Throwable $e) {
            return ['status' => 'FAILED', 'note' => 'Could not connect: ' . $e->getMessage()];
        }
    }

    protected function checkAppDebug(): array
    {
        if (config('app.debug')) {
            return ['status' => 'WARNING', 'note' => 'APP_DEBUG is enabled; disable in production.'];
        }

        return ['status' => 'CONNECTED', 'note' => 'APP_DEBUG disabled.'];
    }

    protected function checkSecureCookie(): array
    {
        if (config('session.secure') !== true && app()->environment('production')) {
            return ['status' => 'WARNING', 'note' => 'SESSION_SECURE_COOKIE must be true in production.'];
        }

        return ['status' => 'CONNECTED', 'note' => 'Secure session cookie configured.'];
    }

    protected function diskUsage(): array
    {
        $path = storage_path('app');

        $total = @disk_total_space($path);
        $free = @disk_free_space($path);

        if ($total === false) {
            return ['total' => '—', 'free' => '—', 'percent' => 0];
        }

        $used = $total - $free;
        $percent = (int) round($used / $total * 100);

        return [
            'total' => $this->formatBytes($total),
            'free' => $this->formatBytes($free),
            'percent' => $percent,
        ];
    }

    protected function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
