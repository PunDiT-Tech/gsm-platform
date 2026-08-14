@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <h2 class="text-xl font-semibold text-gray-900 mb-1">Create account</h2>
    <p class="text-sm text-gray-500 mb-6">Join {{ config('app.name') }} to place and track orders.</p>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>

        <label class="flex items-start text-sm text-gray-600">
            <input type="checkbox" name="terms" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="ml-2">I accept the <a href="{{ route('page', 'terms') }}" class="text-blue-600 hover:underline">Terms &amp; Conditions</a></span>
        </label>

        <button type="submit"
            class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-md hover:bg-blue-700 text-sm font-medium">Create account</button>

        <p class="text-sm text-gray-600 text-center">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Sign in</a>
        </p>
    </form>
@endsection
