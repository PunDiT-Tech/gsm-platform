@extends('layouts.admin')

@section('title', 'Edit Announcement')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit announcement</h1>
        @include('admin.announcements._form', ['action' => route('admin.announcements.update', $announcement), 'method' => 'PUT', 'announcement' => $announcement])
    </div>
@endsection