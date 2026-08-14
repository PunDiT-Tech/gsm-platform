<?php

namespace App\Http\Controllers;

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
            'completed' => $customer?->orders()->where('status', 'COMPLETED')->count() ?? 0,
        ];

        return view('dashboard', compact('orders', 'counts'));
    }
}
