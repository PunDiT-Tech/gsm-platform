@extends('layouts.dashboard')

@section('title', 'Two-Factor Authentication')

@section('panel')
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('profile.security') }}" class="text-sm text-blue-600 hover:underline">← Back to security</a>
            <h2 class="text-2xl font-bold text-gray-900 mt-1">Two-Factor Authentication</h2>
        </div>
        @if ($enabled)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">Enabled</span>
        @else
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">Disabled</span>
        @endif
    </div>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    @if (session('recovery_codes'))
        <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-md">
            <p class="font-semibold mb-2">Recovery codes — save these somewhere safe. Each can be used once.</p>
            <ul class="font-mono space-y-1">
                @foreach (session('recovery_codes') as $code)
                    <li>{{ $code }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $enabled)
        <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-lg space-y-4">
            <h3 class="font-semibold text-gray-900">Set up two-factor authentication</h3>
            <ol class="text-sm text-gray-600 list-decimal pl-5 space-y-2">
                <li>Open your authenticator app (Google Authenticator, Authy, 1Password, etc.).</li>
                <li>Scan the QR code below, or enter the secret key manually.</li>
                <li>Enter the 6-digit code the app shows to confirm setup.</li>
            </ol>

            <div class="flex justify-center bg-gray-50 rounded-lg p-4">
                <div class="bg-white p-3 rounded">{!! $qrSvg !!}</div>
            </div>

            <div>
                <p class="text-xs text-gray-500 uppercase">Manual setup key</p>
                <p class="font-mono text-sm text-gray-800 break-all mt-1">{{ $secret }}</p>
            </div>

            <form method="POST" action="{{ route('profile.two-factor.enable') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">Generate a new key</button>
            </form>

            <form method="POST" action="{{ route('profile.two-factor.confirm') }}" class="space-y-3">
                @csrf
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">6-digit code</label>
                    <input id="code" type="text" name="code" required autocomplete="one-time-code" inputmode="numeric"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">Confirm and enable</button>
            </form>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-lg space-y-4">
            <p class="text-sm text-gray-600">Two-factor authentication is active for your account. On your next login you will be asked for a code from your authenticator app.</p>

            <form method="POST" action="{{ route('profile.two-factor.disable') }}" class="space-y-3">
                @csrf
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current password</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm font-medium">Disable two-factor authentication</button>
            </form>
        </div>
    @endif
@endsection