@extends('layouts.admin')

@section('title', 'Telegram Settings')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Telegram settings</h1>

        @if (session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.telegram.update') }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            @csrf

            <label class="flex items-center text-sm text-gray-700">
                <input type="checkbox" name="enabled" value="1" @checked($setting->enabled) class="rounded border-gray-300 text-blue-600">
                <span class="ml-2">Enable Telegram notifications</span>
            </label>

            <div>
                <label for="bot_token" class="block text-sm font-medium text-gray-700">Bot token <span class="text-gray-400">(encrypted; leave blank to keep existing)</span></label>
                <input id="bot_token" type="password" name="bot_token" value="{{ $setting->bot_token ? '••••••••' : '' }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>

            <div>
                <label for="chat_id" class="block text-sm font-medium text-gray-700">Chat ID</label>
                <input id="chat_id" type="text" name="chat_id" value="{{ $setting->chat_id }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notify on events</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($events as $key => $label)
                        <label class="flex items-center text-sm text-gray-700">
                            <input type="checkbox" name="events[]" value="{{ $key }}" @checked(in_array($key, $setting->events ?? [], true))
                                class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-md hover:bg-blue-700 text-sm font-medium">Save settings</button>
        </form>
    </div>
@endsection