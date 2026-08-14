@extends('layouts.admin')

@section('title', 'New Announcement')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">New announcement</h1>
        @include('admin.announcements._form', ['action' => route('admin.announcements.store'), 'method' => 'POST', 'announcement' => null])
    </div>
@endsection