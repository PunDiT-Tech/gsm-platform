@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Announcements</h1>

        @if ($announcements->isEmpty())
            <p class="text-gray-500">No announcements at this time.</p>
        @else
            <div class="space-y-4">
                @foreach ($announcements as $announcement)
                    <div class="bg-white border border-gray-200 rounded-lg p-5">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $announcement->type }}</span>
                            <span class="text-xs text-gray-400">{{ $announcement->created_at->format('M d, Y') }}</span>
                        </div>
                        <h2 class="font-semibold text-gray-900">{{ $announcement->title }}</h2>
                        <p class="text-sm text-gray-600 mt-1">{{ $announcement->message }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
