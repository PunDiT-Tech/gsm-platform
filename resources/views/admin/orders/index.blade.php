@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Orders</h1>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search order number, customer, IMEI / serial…"
            class="flex-1 min-w-[240px] rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Search</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Order</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Customer</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Service</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Payment</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Amount</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs text-blue-600 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3">{{ $order->service_name_snapshot }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ str_replace('_', ' ', $order->status) }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ str_replace('_', ' ', $order->payment_status) }}</td>
                        <td class="px-4 py-3">{{ $order->currency_snapshot }} {{ number_format((float) $order->price_snapshot, 2) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection