@extends('layouts.dashboard')

@section('title', 'Order ' . $order->order_number)

@section('panel')
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('orders.index') }}" class="text-sm text-blue-600 hover:underline">← My orders</a>
            <h2 class="text-2xl font-bold text-gray-900 mt-1">{{ $order->order_number }}</h2>
            <p class="text-sm text-gray-500">{{ $order->service_name_snapshot }}</p>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">{{ str_replace('_', ' ', $order->status) }}</span>
    </div>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <p class="text-xs text-gray-500 uppercase">Status</p>
            <p class="font-medium mt-1">{{ str_replace('_', ' ', $order->status) }}</p>
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

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Submitted details</h3>
        @if ($order->fieldValues->isEmpty())
            <p class="text-sm text-gray-500">No details submitted.</p>
        @else
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                @foreach ($order->fieldValues as $value)
                    <div>
                        <dt class="text-gray-500">{{ $value->label }}</dt>
                        <dd class="font-medium break-all">{{ $value->value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>

    @if ($order->status === 'WAITING_FOR_CUSTOMER')
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-6">
            <strong>Action required:</strong> The support team has requested additional information. Please reply below or upload the requested files.
        </div>
    @endif

    @if ($order->payments->where('status', 'UNPAID')->isNotEmpty())
        <div class="mb-6">
            <a href="{{ route('orders.pay', $order) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Pay now</a>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Timeline</h3>
        <ol class="space-y-3 text-sm">
            @foreach ($order->statusHistory as $history)
                <li class="flex gap-3">
                    <span class="w-2 h-2 rounded-full bg-blue-600 mt-1.5 shrink-0"></span>
                    <div>
                        <p class="font-medium">{{ $history->from_status ? $history->from_status . ' → ' : '' }}{{ $history->to_status }}</p>
                        @if ($history->note)<p class="text-gray-600">{{ $history->note }}</p>@endif
                        <p class="text-xs text-gray-400">{{ optional($history->created_at)->format('M d, Y H:i') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>

    @if ($order->results->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Results</h3>
            <div class="space-y-3">
                @foreach ($order->results as $result)
                    <div class="p-3 rounded-md bg-green-50 border border-green-200 text-sm">
                        <p class="font-medium">{{ $result->type }}</p>
                        <p class="mt-0.5 text-gray-700 whitespace-pre-wrap break-all">{{ $result->content }}</p>
                        @if ($result->file_path)
                            <a href="{{ route('orders.result-download', $result) }}" class="text-blue-600 text-xs hover:underline">Download</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Messages</h3>
        <div class="space-y-3 mb-4">
            @forelse ($order->messages as $message)
                <div class="text-sm p-3 rounded-md {{ $message->type === 'INTERNAL' ? 'hidden' : 'bg-gray-50' }}">
                    <p class="font-medium">{{ $message->type }} <span class="text-gray-400 font-normal">· {{ $message->created_at->format('M d, H:i') }}</span></p>
                    <p class="mt-0.5 text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
                    @if ($message->attachment_path)
                        <a href="{{ route('orders.message-download', $message) }}" class="text-blue-600 text-xs hover:underline">Download attachment</a>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No messages yet.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('orders.message', $order) }}" class="space-y-2">
            @csrf
            <textarea name="message" rows="2" required placeholder="Write a message to support…"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Send</button>
        </form>
    </div>
@endsection