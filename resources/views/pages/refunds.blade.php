@extends('layouts.app')

@section('title', 'Refund Policy')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Refund Policy</h1>
        <p class="text-sm text-gray-500 mb-6">Last updated: {{ date('F d, Y') }}</p>
        <div class="text-gray-700 space-y-4 text-sm leading-relaxed">
            <p>Refunds are handled on a case-by-case basis by our support team. You may be eligible for a refund if:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>The service could not be completed due to an error on our side.</li>
                <li>A paid order was cancelled before work began.</li>
            </ul>
            <p>Refund requests can be submitted through our support system and are reviewed by authorized staff. Approved refunds are processed via the original payment method where possible.</p>
        </div>
    </div>
@endsection
