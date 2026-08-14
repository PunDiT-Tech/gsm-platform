@extends('layouts.app')

@section('title', 'FAQ')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Frequently asked questions</h1>

        @if ($faqs->isEmpty())
            <p class="text-gray-500">No FAQs published yet.</p>
        @else
            <div class="space-y-3">
                @foreach ($faqs->groupBy('category') as $category => $items)
                    @if ($category)
                        <h2 class="text-lg font-semibold text-gray-800 mt-6">{{ $category }}</h2>
                    @endif
                    @foreach ($items as $faq)
                        <details class="bg-white border border-gray-200 rounded-lg p-4">
                            <summary class="font-medium text-gray-900 cursor-pointer">{{ $faq->question }}</summary>
                            <p class="text-sm text-gray-600 mt-2">{{ $faq->answer }}</p>
                        </details>
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>
@endsection
