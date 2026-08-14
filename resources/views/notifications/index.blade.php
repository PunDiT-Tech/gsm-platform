@extends('layouts.dashboard')

@section('title', 'Notifications')

@section('panel')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Notifications</h2>
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button class="text-blue-600 text-sm hover:underline">Mark all as read</button>
        </form>
    </div>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden divide-y divide-gray-200">
        @forelse ($notifications as $notification)
            <div class="flex items-start justify-between gap-4 p-4 {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                <div class="text-sm">
                    <p class="font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                    <p class="text-gray-600 mt-0.5">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @if (! $notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button class="text-blue-600 text-xs hover:underline shrink-0">Mark read</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">No notifications.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection