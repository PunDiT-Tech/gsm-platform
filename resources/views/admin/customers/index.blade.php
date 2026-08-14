@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Customers</h1>

    <form method="GET" action="{{ route('admin.customers.index') }}" class="flex gap-3 mb-6">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email or phone…"
            class="flex-1 max-w-md rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Search</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Name</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Email</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Phone</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Orders</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Registered</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('admin.customers.show', $customer) }}" class="text-blue-600 hover:underline font-medium">{{ $customer->name }}</a></td>
                        <td class="px-4 py-3">{{ $customer->email }}</td>
                        <td class="px-4 py-3">{{ $customer->phone }}</td>
                        <td class="px-4 py-3">{{ $customer->orders_count }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $customer->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection