@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Reports</h1>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="date" name="from" value="{{ $from?->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm text-sm">
        <input type="date" name="to" value="{{ $to?->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm text-sm">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Run report</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        @foreach ([
            'total_orders' => ['Total orders', $stats['total_orders']],
            'completed_orders' => ['Completed', $stats['completed_orders']],
            'pending_orders' => ['Pending', $stats['pending_orders']],
            'cancelled_orders' => ['Cancelled/Rejected', $stats['cancelled_orders']],
            'revenue' => ['Revenue (completed)', '$' . number_format((float) $stats['revenue'], 2)],
        ] as [$label, $value])
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Revenue by service (completed)</h2>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-600 font-medium">Service</th>
                        <th class="px-3 py-2 text-left text-gray-600 font-medium">Orders</th>
                        <th class="px-3 py-2 text-right text-gray-600 font-medium">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($revenueByService as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row->service_name_snapshot }}</td>
                            <td class="px-3 py-2">{{ $row->order_count }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format((float) $row->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-center text-gray-500">No completed orders in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Orders by payment method</h2>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-600 font-medium">Method</th>
                        <th class="px-3 py-2 text-right text-gray-600 font-medium">Payments</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($paymentsByMethod as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row->method?->name ?? 'Unknown' }}</td>
                            <td class="px-3 py-2 text-right">{{ $row->total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-3 py-6 text-center text-gray-500">No payments in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection