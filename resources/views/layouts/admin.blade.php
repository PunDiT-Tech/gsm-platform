<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="flex min-h-screen">
        <aside class="w-60 bg-gray-900 text-gray-300 shrink-0 hidden lg:block">
            <div class="px-5 py-5 text-white font-bold text-lg border-b border-gray-800">{{ config('app.name') }} <span class="text-blue-400 text-xs align-top">Admin</span></div>
            <nav class="p-3 space-y-0.5 text-sm">
                    @php
                        $items = [
                            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '📊', 'permission' => null],
                            ['route' => 'admin.orders.index', 'label' => 'Orders', 'icon' => '🧾', 'permission' => 'orders.view'],
                            ['route' => 'admin.services.index', 'label' => 'Services', 'icon' => '🔧', 'permission' => 'services.view'],
                            ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => '🗂️', 'permission' => 'services.view'],
                            ['route' => 'admin.payments.index', 'label' => 'Payments', 'icon' => '💳', 'permission' => 'payments.view'],
                            ['route' => 'admin.customers.index', 'label' => 'Customers', 'icon' => '👥', 'permission' => 'customers.view'],
                            ['route' => 'admin.support.index', 'label' => 'Support', 'icon' => '🎧', 'permission' => 'support.view'],
                            ['route' => 'admin.homepage.index', 'label' => 'Homepage', 'icon' => '🏠', 'permission' => 'homepage.manage'],
                            ['route' => 'admin.announcements.index', 'label' => 'Announcements', 'icon' => '📢', 'permission' => 'announcements.manage'],
                            ['route' => 'admin.faq.index', 'label' => 'FAQ', 'icon' => '❓', 'permission' => 'homepage.manage'],
                            ['route' => 'admin.telegram.index', 'label' => 'Telegram', 'icon' => '✈️', 'permission' => 'telegram.manage'],
                            ['route' => 'admin.reports.index', 'label' => 'Reports', 'icon' => '📈', 'permission' => 'reports.view'],
                            ['route' => 'admin.staff.index', 'label' => 'Admin Users', 'icon' => '🛡️', 'permission' => 'admins.manage'],
                            ['route' => 'admin.settings.index', 'label' => 'Settings', 'icon' => '⚙️', 'permission' => 'settings.manage'],
                            ['route' => 'admin.system.index', 'label' => 'System', 'icon' => '🖥️', 'permission' => 'settings.manage'],
                            ['route' => 'admin.audit-logs.index', 'label' => 'Audit Logs', 'icon' => '📋', 'permission' => 'audit_logs.view'],
                        ];
                    @endphp
                    @foreach ($items as $item)
                        @if ($item['permission'] === null || auth()->user()->hasPermission($item['permission']))
                            @php $active = request()->routeIs($item['route'] . '*'); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-2 px-3 py-2 rounded-md {{ $active ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                                <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="text-gray-700 text-sm">Welcome, <strong>{{ auth()->user()->name }}</strong></div>
                <div class="flex items-center gap-4 text-sm">
                    <a href="/" class="text-gray-600 hover:text-blue-600">View site</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-blue-600">Logout</button>
                    </form>
                </div>
            </header>

            @if (session('status'))
                <div class="max-w-7xl mx-auto w-full mt-4 px-6">
                    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
                </div>
            @endif

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
