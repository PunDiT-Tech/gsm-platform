@extends('layouts.admin')

@section('title', 'Admin Users')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Admin users</h1>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Create staff member</h2>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required min="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm password</label>
                <input type="password" name="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Roles</label>
                <select name="roles[]" multiple required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Create</button>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Name</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Email</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Roles</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Active</th>
                    <th class="px-4 py-3 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @foreach ($user->roles as $role)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 mr-1">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.staff.update', $user) }}">
                                @csrf
                                @method('PUT')
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="is_active" value="1" @checked($user->is_active) onchange="this.form.submit()" class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.staff.destroy', $user) }}" onsubmit="return confirm('Deactivate this staff member?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">Roles & permissions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($roles as $role)
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 text-sm mb-2">{{ $role->label }}</h3>
                    <div class="flex flex-wrap gap-1">
                        @foreach ($role->permissions as $permission)
                            <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-xs">{{ $permission->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection