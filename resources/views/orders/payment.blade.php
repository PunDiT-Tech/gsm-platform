@extends('layouts.app')

@section('title', 'Payment — Order ' . $order->order_number)

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment required</h1>
        <p class="text-gray-500 mb-6">Order <strong class="font-mono">{{ $order->order_number }}</strong> · {{ $order->service_name_snapshot }}</p>

        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <div class="flex justify-between text-sm mb-4">
                <span class="text-gray-500">Amount due</span>
                <span class="font-bold text-lg text-gray-900">{{ $order->currency_snapshot }} {{ number_format((float) $order->price_snapshot, 2) }}</span>
            </div>

            @foreach ($order->payments as $payment)
                <div class="border border-gray-200 rounded-md p-4 mb-4">
                    <p class="font-medium text-gray-900">{{ $payment->method?->name ?? 'Manual payment' }}</p>
                    @if ($payment->method?->instructions)
                        <p class="text-sm text-gray-600 mt-1 whitespace-pre-wrap">{{ $payment->method->instructions }}</p>
                    @endif
                    <p class="mt-2"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Status: {{ $payment->status }}</span></p>
                </div>
            @endforeach
        </div>

        @if (session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
                <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Submit payment proof</h2>
            <form method="POST" action="{{ route('orders.payment.upload', $order) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Payment</label>
                    <select name="payment_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @foreach ($order->payments as $payment)
                            <option value="{{ $payment->id }}">{{ $payment->method?->name ?? 'Manual' }} — {{ $payment->amount }} {{ $payment->currency }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Transaction ID</label>
                    <input type="text" name="transaction_id" value="{{ old('transaction_id') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Receipt / screenshot (JPG, PNG, PDF — max 10MB)</label>
                    <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-md hover:bg-blue-700 text-sm font-medium">Submit proof</button>
            </form>
        </div>
    </div>
@endsection
