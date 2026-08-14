@extends('layouts.app')

@section('title', 'Acceptable Use Policy')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Acceptable Use Policy</h1>
        <p class="text-sm text-gray-500 mb-6">Last updated: {{ date('F d, Y') }}</p>
        <div class="text-gray-700 space-y-4 text-sm leading-relaxed">
            <p>{{ config('app.name') }} provides legitimate GSM repair, diagnostic and maintenance services. The following uses are strictly prohibited:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>Attempting to gain unauthorized access to devices, accounts or data.</li>
                <li>Bypassing or removing security protections on devices you do not own or are not authorized to service.</li>
                <li>Submitting fraudulent or fabricated order information.</li>
                <li>Using the platform to facilitate any illegal activity.</li>
            </ul>
            <p>By submitting an order you confirm you are the owner of the device or are legally authorized to service it. Violations may result in order cancellation and account suspension.</p>
        </div>
    </div>
@endsection
