@extends('layouts.dashboard')

@section('title', 'My Orders')

@section('panel')
    <h2 class="text-2xl font-bold text-gray-900 mb-6">My orders</h2>

    @if ($orders->isEmpty())
        <div class="bg-white border border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
            You have no orders yet.
            <a href="{{ route('services.index') }}" class="text-blue-600 hover:underline block mt-2">Browse services</a>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Order</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Service</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Payment</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><a href="{{ route('orders.show', $order) }}" class="font-mono text-xs text-blue-600 hover:underline">{{ $order->order_number }}</a></td>
                            <td class="px-4 py-3">{{ $order->service_name_snapshot }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ str_replace('_', ' ', $order->status) }}</span></td>
                            <td class="px-4 py-3 text-gray-500">{{ str_replace('_', ' ', $order->payment_status) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection