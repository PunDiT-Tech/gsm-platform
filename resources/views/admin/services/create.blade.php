@extends('layouts.admin')

@section('title', 'New Service')

@section('content')
    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">New service</h1>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
                <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.services.store') }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            @csrf
            @include('admin.services._form', ['service' => null, 'categories' => $categories])
            <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-md hover:bg-blue-700 text-sm font-medium">Create service</button>
        </form>
    </div>
@endsection
