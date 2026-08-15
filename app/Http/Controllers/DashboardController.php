<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Customer;
use App\Models\OrderMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        $orders = $customer?->orders()->latest()->limit(5)->get() ?? collect();

        $counts = [
            'total' => $customer?->orders()->count() ?? 0,
            'pending' => $customer?->orders()->where('status', 'PENDING')->count() ?? 0,
            'processing' => $customer?->orders()->where('status', 'PROCESSING')->count() ?? 0,
            'waiting' => $customer?->orders()->where('status', 'WAITING_FOR_CUSTOMER')->count() ?? 0,
            'completed' => $customer?->orders()->where('status', 'COMPLETED')->count() ?? 0,
            'rejected' => $customer?->orders()->where('status', 'REJECTED')->count() ?? 0,
            'cancelled' => $customer?->orders()->where('status', 'CANCELLED')->count() ?? 0,
        ];

        $unread = $this->unreadCount($customer);

        $announcements = Announcement::active('dashboard')->latest()->limit(3)->get();

        return view('dashboard', compact('orders', 'counts', 'unread', 'announcements'));
    }

    protected function unreadCount(?Customer $customer): int
    {
        if (! $customer) {
            return 0;
        }

        return OrderMessage::whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))
            ->where('type', 'CUSTOMER')
            ->whereNotNull('user_id')
            ->whereNull('read_at')
            ->count();
    }
}
