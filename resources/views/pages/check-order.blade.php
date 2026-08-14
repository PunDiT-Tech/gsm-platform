@extends('layouts.app')

@section('title', 'Check Order')

@section('content')
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Track your order</h1>
        <p class="text-gray-500 mb-8">Enter your order number and tracking code to view the current status.</p>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('order.lookup.submit') }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            @csrf
            <div>
                <label for="order_number" class="block text-sm font-medium text-gray-700">Order number</label>
                <input id="order_number" type="text" name="order_number" value="{{ old('order_number') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label for="tracking_token" class="block text-sm font-medium text-gray-700">Tracking code</label>
                <input id="tracking_token" type="text" name="tracking_token" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-md hover:bg-blue-700 text-sm font-medium">Track order</button>
        </form>
    </div>
@endsection
