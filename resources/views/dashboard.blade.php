@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('panel')
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Welcome, {{ auth()->user()->name }}</h2>

    @if ($announcements->isNotEmpty())
        <div class="mb-8 space-y-3">
            @foreach ($announcements as $announcement)
                <div class="border border-gray-200 rounded-lg p-4 bg-white">
                    <p class="font-medium text-gray-900 text-sm">{{ $announcement->title }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $announcement->message }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach ([
            'total' => ['Total Orders', $counts['total']],
            'pending' => ['Pending', $counts['pending']],
            'processing' => ['Processing', $counts['processing']],
            'waiting' => ['Waiting', $counts['waiting']],
            'completed' => ['Completed', $counts['completed']],
            'rejected' => ['Rejected', $counts['rejected']],
            'cancelled' => ['Cancelled', $counts['cancelled']],
        ] as $key => [$label, $value])
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <h3 class="text-lg font-semibold text-gray-900 mb-3">Recent orders</h3>

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
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $order->order_number }}</td>
                            <td class="px-4 py-3">{{ $order->service_name_snapshot }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ str_replace('_', ' ', $order->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
