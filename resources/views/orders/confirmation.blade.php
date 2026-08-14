@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 text-center">
        <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 text-3xl flex items-center justify-center mx-auto mb-4">✓</div>
        <h1 class="text-2xl font-bold text-gray-900">Order submitted successfully</h1>
        <p class="text-gray-500 mt-2">Thank you. Keep your order number and tracking code safe.</p>

        <div class="mt-8 bg-white border border-gray-200 rounded-lg p-6 text-left space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Order number</span>
                <span class="font-mono font-semibold text-gray-900">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Tracking code</span>
                <span class="font-mono font-semibold text-gray-900">{{ $token }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Service</span>
                <span class="font-medium text-gray-900">{{ $order->service_name_snapshot }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Status</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ str_replace('_', ' ', $order->status) }}</span>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('order.lookup') }}" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 text-sm font-medium">Track your order</a>
            <a href="{{ route('services.index') }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-md hover:border-blue-400 text-sm font-medium">Browse more services</a>
        </div>
    </div>
@endsection
