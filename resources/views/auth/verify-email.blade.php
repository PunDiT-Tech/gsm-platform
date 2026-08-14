@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
    <h2 class="text-xl font-semibold text-gray-900 mb-2">Verify your email</h2>
    <p class="text-sm text-gray-600 mb-6">
        We have sent a verification link to your email address. Please click it to activate your account. If you did not receive it, request another.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
        @csrf
        <button type="submit" class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-md hover:bg-blue-700 text-sm font-medium">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full text-gray-500 hover:text-gray-700 text-sm">Logout</button>
    </form>
@endsection
