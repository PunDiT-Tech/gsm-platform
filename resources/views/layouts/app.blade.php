<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', config('app.name') . ' — Professional GSM device repair, diagnostics and maintenance services.')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
                <a href="/" class="text-xl font-bold text-blue-600">{{ config('app.name') }}</a>
                <div class="flex items-center gap-4 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-blue-600">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('services.index') }}" class="text-gray-700 hover:text-blue-600">Services</a>
                        <a href="{{ route('faq') }}" class="text-gray-700 hover:text-blue-600">FAQ</a>
                        <a href="{{ route('contact') }}" class="text-gray-700 hover:text-blue-600">Contact</a>
                        <a href="{{ route('order.lookup') }}" class="text-gray-700 hover:text-blue-600">Track Order</a>
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">Login</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Sign up</a>
                    @endauth
                </div>
            </nav>
        </header>

        @if (session('status'))
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6">
                <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
            </div>
        @endif

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="bg-white border-t border-gray-200 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between text-sm text-gray-500 gap-4">
                <p>&copy; {{ date('Y') }} {{ \App\Models\HomepageSection::where('key', 'footer')->value('title') ?: config('app.name') }}. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="{{ route('page', 'terms') }}" class="hover:text-blue-600">Terms</a>
                    <a href="{{ route('page', 'privacy') }}" class="hover:text-blue-600">Privacy</a>
                    <a href="{{ route('page', 'refunds') }}" class="hover:text-blue-600">Refunds</a>
                    <a href="{{ route('page', 'acceptable-use') }}" class="hover:text-blue-600">Acceptable Use</a>
                </div>
            </div>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
