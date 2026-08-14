@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-8">
        <aside class="lg:border-r lg:border-gray-200 lg:pr-6">
            <nav class="flex lg:flex-col gap-2 text-sm overflow-x-auto">
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Overview</a>
                <a href="{{ route('orders.index') }}"
                    class="px-4 py-2 rounded-md {{ request()->routeIs('orders.*') ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">My Orders</a>
                <a href="{{ route('notifications.index') }}"
                    class="px-4 py-2 rounded-md flex items-center justify-between gap-2 {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span>Notifications</span>
                    @auth
                        @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                        @if ($unread > 0)
                            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full text-xs font-semibold {{ request()->routeIs('notifications.*') ? 'bg-white text-blue-600' : 'bg-red-500 text-white' }}">{{ $unread > 99 ? '99+' : $unread }}</span>
                        @endif
                    @endauth
                </a>
                <a href="{{ route('support.index') }}"
                    class="px-4 py-2 rounded-md {{ request()->routeIs('support.*') ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Support</a>
                <a href="{{ route('profile.edit') }}"
                    class="px-4 py-2 rounded-md {{ request()->routeIs('profile.edit') ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Profile</a>
                <a href="{{ route('profile.security') }}"
                    class="px-4 py-2 rounded-md {{ request()->routeIs('profile.security') ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Security</a>
            </nav>
        </aside>

        <section>
            @yield('panel')
        </section>
    </div>
@endsection
