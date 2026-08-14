@extends('layouts.app')

@section('title', 'How it works')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">How it works</h1>
        <div class="space-y-6">
            @foreach ([
                ['Choose a service', 'Browse our catalog, read the service details and confirm it matches your needs.'],
                ['Fill in the order form', 'Provide the requested device details. All submitted information is treated confidentially.'],
                ['Review and pay', 'Review your order, choose a payment method and upload proof of payment where required.'],
                ['Track your order', 'Use your order number and tracking code to follow progress in real time.'],
                ['Receive the result', 'Once completed, the result is delivered securely on your order page.'],
            ] as [$title, $text])
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="font-semibold text-gray-900">{{ $title }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
