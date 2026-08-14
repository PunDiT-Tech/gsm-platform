@extends('layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 prose">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Terms & Conditions</h1>
        <p class="text-sm text-gray-500 mb-6">Last updated: {{ date('F d, Y') }}</p>
        <div class="text-gray-700 space-y-4 text-sm leading-relaxed">
            <p>By using {{ config('app.name') }}, you agree to these terms. You confirm that you are legally authorized to request services for any device you submit.</p>
            <p>Services are provided for legitimate device repair, diagnostics and maintenance. You may not use the platform for unauthorized access to devices, accounts or data.</p>
            <p>All information you provide must be accurate. {{ config('app.name') }} is not responsible for losses caused by incorrect submissions.</p>
            <p>We may suspend accounts that violate these terms or engage in fraudulent activity.</p>
        </div>
    </div>
@endsection
