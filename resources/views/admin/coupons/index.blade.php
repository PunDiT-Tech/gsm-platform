@extends('layouts.admin')

@section('title', 'Coupons')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Coupons</h1>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Create coupon</h2>
        <form method="POST" action="{{ route('admin.coupons.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" name="code" required placeholder="SAVE10"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="PERCENT">Percent (%)</option>
                    <option value="FIXED">Fixed amount</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Value</label>
                <input type="number" name="value" step="0.01" min="0.01" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Usage limit <span class="text-gray-400">(blank = unlimited)</span></label>
                <input type="number" name="usage_limit" min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Per-customer limit</label>
                <input type="number" name="per_customer_limit" min="1" value="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Restrict to service <span class="text-gray-400">(optional)</span></label>
                <select name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">All services</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Expires at <span class="text-gray-400">(optional)</span></label>
                <input type="datetime-local" name="expires_at"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Create</button>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Code</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Type</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Value</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Service</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Used</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Expires</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Active</th>
                    <th class="px-4 py-3 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($coupons as $coupon)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-medium">{{ $coupon->code }}</td>
                        <td class="px-4 py-3">{{ $coupon->type }}</td>
                        <td class="px-4 py-3">{{ $coupon->type === 'PERCENT' ? $coupon->value . '%' : '$' . $coupon->value }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $coupon->service?->name ?? 'All services' }}</td>
                        <td class="px-4 py-3">{{ $coupon->usages_count }}@if ($coupon->usage_limit) / {{ $coupon->usage_limit }}@endif</td>
                        <td class="px-4 py-3 text-gray-500">{{ $coupon->expires_at?->format('M d, Y') ?? 'Never' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="code" value="{{ $coupon->code }}">
                                <input type="hidden" name="type" value="{{ $coupon->type }}">
                                <input type="hidden" name="value" value="{{ $coupon->value }}">
                                <input type="hidden" name="service_id" value="{{ $coupon->service_id ?? '' }}">
                                <button class="px-2 py-0.5 rounded-full text-xs {{ $coupon->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}" name="is_active" value="1" onchange="this.form.submit()">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete coupon?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No coupons.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $coupons->links() }}</div>
@endsection