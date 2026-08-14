@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach ([
            'total_orders' => ['Total orders', $stats['total_orders']],
            'pending_orders' => ['Pending', $stats['pending_orders']],
            'processing_orders' => ['Processing', $stats['processing_orders']],
            'completed_orders' => ['Completed', $stats['completed_orders']],
            'proofs_awaiting' => ['Proofs awaiting review', $stats['proofs_awaiting']],
            'customers' => ['Customers', $stats['customers']],
            'revenue' => ['Revenue (completed)', $stats['revenue']],
        ] as $key => [$label, $value])
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ is_float($value) || is_numeric($value) && !is_int($value) ? '$' . number_format((float) $value, 2) : $value }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent orders</h2>
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Order</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Customer</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Service</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Amount</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($recentOrders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs text-blue-600 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3">{{ $order->service_name_snapshot }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ str_replace('_', ' ', $order->status) }}</span></td>
                        <td class="px-4 py-3">{{ $order->currency_snapshot }} {{ number_format((float) $order->price_snapshot, 2) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
