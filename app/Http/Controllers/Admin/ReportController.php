<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();
        $serviceId = $request->integer('service_id') ?: null;

        $orders = Order::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->when($serviceId, fn ($q) => $q->where('service_id', $serviceId));

        $stats = [
            'total_orders' => (clone $orders)->count(),
            'completed_orders' => (clone $orders)->where('status', 'COMPLETED')->count(),
            'pending_orders' => (clone $orders)->where('status', 'PENDING')->count(),
            'cancelled_orders' => (clone $orders)->whereIn('status', ['CANCELLED', 'REJECTED'])->count(),
            'revenue' => (clone $orders)->where('status', 'COMPLETED')->sum('price_snapshot'),
        ];

        $revenueByService = (clone $orders)
            ->select('service_name_snapshot')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(price_snapshot) as revenue')
            ->where('status', 'COMPLETED')
            ->groupBy('service_name_snapshot')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $paymentsByMethod = Payment::when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->select('payment_method_id')
            ->with('method')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('payment_method_id')
            ->get();

        return view('admin.reports.index', compact('stats', 'revenueByService', 'paymentsByMethod', 'from', 'to'));
    }
}