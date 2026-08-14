@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Announcements</h1>
        <a href="{{ route('admin.announcements.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">New announcement</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Title</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Type</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Location</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Active</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Window</th>
                    <th class="px-4 py-3 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($announcements as $announcement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $announcement->title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $announcement->type }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $announcement->location }}</td>
                        <td class="px-4 py-3">{{ $announcement->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ optional($announcement->starts_at)->format('M d') }} → {{ optional($announcement->ends_at)->format('M d') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="inline" onsubmit="return confirm('Delete?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No announcements.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $announcements->links() }}</div>
@endsection