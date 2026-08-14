<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentProof;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'PENDING')->count(),
            'processing_orders' => Order::where('status', 'PROCESSING')->count(),
            'completed_orders' => Order::where('status', 'COMPLETED')->count(),
            'proofs_awaiting' => PaymentProof::whereHas('payment', fn ($q) => $q->where('status', 'PROOF_SUBMITTED'))->count(),
            'customers' => Customer::count(),
            'revenue' => Order::where('status', 'COMPLETED')->sum('price_snapshot'),
        ];

        $recentOrders = Order::with('customer')->latest()->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
