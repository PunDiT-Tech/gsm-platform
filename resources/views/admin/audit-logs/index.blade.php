@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Audit logs</h1>

    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex gap-3 mb-6">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action…"
            class="flex-1 max-w-md rounded-md border-gray-300 shadow-sm text-sm">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Filter</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Time</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">User</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Action</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Entity</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">ID</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? 'system' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $log->action }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $log->entity_type ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $log->entity_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $log->ip ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No audit logs.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection