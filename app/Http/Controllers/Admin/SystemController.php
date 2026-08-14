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

        return ['status' => 'CONNECTED', 'note' => 'Enabled'];
    }

    protected function checkMail(): array
    {
        return ['status' => 'CONNECTED', 'note' => 'Driver: ' . config('mail.default')];
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
