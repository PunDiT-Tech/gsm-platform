@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Order {{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">Submitted {{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">{{ str_replace('_', ' ', $order->status) }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Service</p>
                <p class="font-medium mt-1">{{ $order->service_name_snapshot }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Payment</p>
                <p class="font-medium mt-1">{{ str_replace('_', ' ', $order->payment_status) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Amount</p>
                <p class="font-medium mt-1">{{ $order->currency_snapshot }} {{ number_format((float) $order->price_snapshot, 2) }}</p>
                @if ($order->coupon_code)
                    <p class="text-xs text-gray-500 mt-1">Coupon: {{ $order->coupon_code }} (discount: -{{ $order->currency_snapshot }} {{ number_format((float) ($order->couponUsage?->discount_amount ?? 0), 2) }})</p>
                @endif
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
            <h2 class="font-semibold text-gray-900 mb-4">Timeline</h2>
            @if ($order->statusHistory->isEmpty())
                <p class="text-sm text-gray-500">Order received.</p>
            @else
                <ol class="space-y-4">
                    @foreach ($order->statusHistory as $history)
                        <li class="flex gap-3">
                            <span class="w-2 h-2 rounded-full bg-blue-600 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="text-sm font-medium">
                                    {{ $history->from_status ? $history->from_status . ' → ' : '' }}{{ $history->to_status }}
                                </p>
                                @if ($history->note)
                                    <p class="text-sm text-gray-600">{{ $history->note }}</p>
                                @endif
                                <p class="text-xs text-gray-400">{{ optional($history->created_at)->format('M d, Y H:i') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        @if ($order->results->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
                <h2 class="font-semibold text-gray-900 mb-4">Order result</h2>
                @foreach ($order->results as $result)
                    <div class="border-t border-gray-100 pt-3 first:border-0 first:pt-0">
                        @if ($result->type === 'FILE')
                            <a href="#" class="text-blue-600 text-sm hover:underline">Download file</a>
                        @elseif ($result->type === 'LINK')
                            <a href="{{ $result->content }}" target="_blank" rel="noopener" class="text-blue-600 text-sm hover:underline">{{ $result->content }}</a>
                        @else
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $result->content }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($order->messages->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Messages</h2>
                <div class="space-y-3">
                    @foreach ($order->messages as $message)
                        <div class="text-sm">
                            <p class="font-medium text-gray-700">{{ $message->type }} <span class="text-gray-400 font-normal">· {{ $message->created_at->format('M d, H:i') }}</span></p>
                            <p class="text-gray-600 mt-0.5">{{ $message->message }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
