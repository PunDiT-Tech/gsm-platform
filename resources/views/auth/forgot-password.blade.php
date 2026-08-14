@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <h2 class="text-xl font-semibold text-gray-900 mb-1">Reset your password</h2>
    <p class="text-sm text-gray-500 mb-6">Enter your email and we will send you a reset link. For security, we do not reveal whether an account exists.</p>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>

        <button type="submit"
            class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-md hover:bg-blue-700 text-sm font-medium">Send reset link</button>

        <p class="text-sm text-gray-600 text-center">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Back to sign in</a>
        </p>
    </form>
@endsection
