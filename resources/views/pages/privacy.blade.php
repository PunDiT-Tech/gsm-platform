@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-6">Last updated: {{ date('F d, Y') }}</p>
        <div class="text-gray-700 space-y-4 text-sm leading-relaxed">
            <p>We collect information you provide (name, email, phone) and the device details you submit in order forms. This data is used solely to fulfill your order.</p>
            <p>Payment proofs and order results are stored in private storage and accessed only by authorized staff and you.</p>
            <p>We implement appropriate technical measures including encryption, access controls and audit logging to protect your data.</p>
            <p>We do not sell your personal data. Contact us to request access or deletion of your data.</p>
        </div>
    </div>
@endsection
