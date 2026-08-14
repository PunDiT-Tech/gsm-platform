<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = request()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request): RedirectResponse
    {
        $request->validate(['id' => ['required', 'uuid']]);

        $notification = request()->user()->notifications()->findOrFail($request->id);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        request()->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }
}
