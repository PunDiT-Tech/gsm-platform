@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        @if ($notifications->isNotEmpty())
            <form method="POST" action="{{ route('admin.notifications.readAll') }}">
                @csrf
                <button class="text-sm text-blue-600 hover:underline">Mark all as read</button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        @forelse ($notifications as $notification)
            <div class="px-4 py-3 border-b border-gray-100 {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-900 font-medium">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                            @csrf
                            <button class="text-xs text-blue-600 hover:underline shrink-0">Mark read</button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <p class="px-4 py-8 text-center text-gray-500">No notifications.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection