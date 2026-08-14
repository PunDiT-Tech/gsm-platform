@extends('layouts.admin')

@section('title', 'Customer ' . $customer->name)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.customers.index') }}" class="text-sm text-blue-600 hover:underline">← Back to customers</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $customer->name }}</h1>
            <p class="text-gray-500 text-sm">{{ $customer->email }} · {{ $customer->phone }}</p>
        </div>
    </div>

    <h2 class="text-lg font-semibold text-gray-900 mb-3">Orders</h2>
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Order</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Service</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Amount</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs text-blue-600 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="px-4 py-3">{{ $order->service_name_snapshot }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ str_replace('_', ' ', $order->status) }}</span></td>
                        <td class="px-4 py-3">{{ $order->currency_snapshot }} {{ number_format((float) $order->price_snapshot, 2) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No orders.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection