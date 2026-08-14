@extends('layouts.admin')

@section('title', 'System Health')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">System health</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach ($checks as $name => $check)
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <p class="font-medium text-gray-900 capitalize">{{ $name }}</p>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $check['status'] === 'CONNECTED' ? 'bg-green-100 text-green-700' : ($check['status'] === 'WARNING' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $check['status'] }}</span>
                </div>
                @if (isset($check['note']))
                    <p class="text-sm text-gray-500 mt-2">{{ $check['note'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="font-semibold text-gray-900 mb-2">Disk usage</h2>
        <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>Total: {{ $diskUsage['total'] }}</span>
            <span>Free: {{ $diskUsage['free'] }}</span>
            <span>{{ $diskUsage['percent'] }}% used</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-blue-600 h-3 rounded-full {{ $diskUsage['percent'] > 90 ? 'bg-red-600' : '' }}" style="width: {{ min($diskUsage['percent'], 100) }}%"></div>
        </div>
    </div>
@endsection