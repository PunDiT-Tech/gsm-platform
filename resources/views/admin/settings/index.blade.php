@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Settings</h1>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Website settings</h2>
            <form method="POST" action="{{ route('admin.settings.website') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contact email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $contactEmail) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contact phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $contactPhone) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unpaid order expiry (hours)</label>
                    <input type="number" name="order_expiry_hours" min="1" max="720" value="{{ old('order_expiry_hours', $orderExpiryHours) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Save</button>
            </form>
        </div>

        <div class="space-y-6">
            @foreach ($methods as $method)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Payment method: {{ $method->name }}</h2>
                    @php $config = (array) ($method->configuration ?? []); @endphp
                    <form method="POST" action="{{ route('admin.settings.methods.update', $method) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="{{ $method->name }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Instructions (shown to customers)</label>
                            <textarea name="instructions" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $method->instructions }}</textarea>
                        </div>

                        @if ($method->code === 'BANK_TRANSFER')
                            <div class="grid grid-cols-2 gap-3">
                                <div><label class="block text-sm font-medium text-gray-700">Account name</label><input type="text" name="account_name" value="{{ $config['account_name'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Account number</label><input type="text" name="account_number" value="{{ $config['account_number'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">SWIFT</label><input type="text" name="swift" value="{{ $config['swift'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Branch</label><input type="text" name="branch" value="{{ $config['branch'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></div>
                            </div>
                        @endif

                        @if ($method->code === 'BINANCE')
                            <div class="grid grid-cols-2 gap-3">
                                <div><label class="block text-sm font-medium text-gray-700">Payment ID</label><input type="text" name="payment_id" value="{{ $config['payment_id'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Network</label><input type="text" name="network" value="{{ $config['network'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></div>
                            </div>
                        @endif

                        @if ($method->code === 'MANUAL_CRYPTO')
                            <div class="grid grid-cols-2 gap-3">
                                <div><label class="block text-sm font-medium text-gray-700">Wallet</label><input type="text" name="wallet" value="{{ $config['wallet'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm font-mono"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Network</label><input type="text" name="network" value="{{ $config['network'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></div>
                            </div>
                        @endif

                        <label class="flex items-center text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" @checked($method->is_active) class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2">Active</span>
                        </label>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Save method</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endsection