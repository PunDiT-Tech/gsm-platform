<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TelegramController extends Controller
{
    public function index(): View
    {
        $setting = TelegramSetting::firstOrCreate(['id' => 1]);

        $events = [
            'new_order' => 'New order',
            'payment_proof' => 'Payment proof',
            'payment_verified' => 'Payment verified',
            'payment_rejected' => 'Payment rejected',
            'processing' => 'Processing',
            'waiting_for_customer' => 'Waiting for customer',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return view('admin.telegram.index', compact('setting', 'events'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = TelegramSetting::firstOrCreate(['id' => 1]);

        $request->validate([
            'enabled' => ['boolean'],
            'bot_token' => ['nullable', 'string'],
            'chat_id' => ['nullable', 'string', 'max:255'],
            'events' => ['nullable', 'array'],
        ]);

        $setting->update([
            'enabled' => $request->boolean('enabled'),
            'bot_token' => $request->bot_token ? encrypt($request->bot_token) : $setting->bot_token,
            'chat_id' => $request->chat_id,
            'events' => $request->input('events', []),
        ]);

        return back()->with('status', 'Telegram settings saved. The bot token is stored encrypted.');
    }
}
