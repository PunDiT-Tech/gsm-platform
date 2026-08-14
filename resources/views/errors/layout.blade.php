<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 text-center">
        <p class="text-6xl font-bold text-blue-600 mb-4">@yield('code')</p>
        <h1 class="text-2xl font-semibold mb-2">@yield('title')</h1>
        <p class="text-gray-500 mb-8 max-w-md">@yield('message')</p>
        <a href="/" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 text-sm font-medium">Back to home</a>
    </div>
</body>
</html>
