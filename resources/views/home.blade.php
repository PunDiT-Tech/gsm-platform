@extends('layouts.app')

@section('title', config('app.name') . ' — Professional Device Services')

@push('head')
<style>
    #showcase-track .showcase-slide .showcase-content > * {
        opacity: 0;
    }
    #showcase-track .showcase-slide.no-anim .showcase-content > * {
        opacity: 1;
        animation: none;
    }
    #showcase-track .showcase-slide.is-active .showcase-content > * {
        opacity: 1;
        animation: var(--sc-anim, showcase-fade) .8s ease-out both;
    }
    #showcase-track .showcase-slide.is-active .showcase-content > *:nth-child(2) { animation-delay: .12s; }
    #showcase-track .showcase-slide.is-active .showcase-content > *:nth-child(3) { animation-delay: .24s; }
    @keyframes showcase-fade {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes showcase-slide {
        from { opacity: 0; transform: translateX(60px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes showcase-zoom {
        from { opacity: 0; transform: scale(.85); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes showcase-float {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes showcase-zoom-fade {
        from { opacity: 0; transform: scale(.9); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes showcase-parallax {
        from { opacity: .4; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const track = document.getElementById('showcase-track');
    if (!track) return;
    const slides = Array.from(track.querySelectorAll('.showcase-slide'));
    if (slides.length <= 1) return;
    const dots = Array.from(document.querySelectorAll('#showcase-dots span'));
    let index = 0;

    function go(i, dir) {
        index = (i + slides.length) % slides.length;
        track.style.transform = `translateX(-${index * 100}%)`;
        slides.forEach((s, j) => {
            s.classList.toggle('is-active', j === index);
            const anim = (s.dataset.animation || 'NONE').toUpperCase();
            s.classList.toggle('no-anim', reduce || anim === 'NONE');
            if (j === index && !reduce && anim !== 'NONE') {
                s.style.setProperty('--sc-anim', `showcase-${anim.toLowerCase()}`);
            }
        });
        dots.forEach((d, j) => {
            d.classList.toggle('bg-white', j === index);
            d.classList.toggle('bg-white/40', j !== index);
        });
    }

    function next() { go(index + 1, 1); }
    function prev() { go(index - 1, -1); }

    let timer = reduce ? null : setInterval(next, 5000);
    const wrap = document.getElementById('showcase-wrap');
    wrap.addEventListener('mouseenter', () => timer && clearInterval(timer));
    wrap.addEventListener('mouseleave', () => { if (!reduce) timer = setInterval(next, 5000); });

    let startX = 0;
    let startY = 0;
    wrap.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });
    wrap.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - startX;
        const dy = e.changedTouches[0].clientY - startY;
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
            if (dx < 0) next(); else prev();
        }
    }, { passive: true });

    go(0);
});
</script>
@endpush

@section('content')
    @if ($announcements->isNotEmpty())
        <div class="bg-blue-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 space-y-1">
                @foreach ($announcements as $a)
                    <p class="text-sm"><strong>{{ $a->title }}:</strong> {{ $a->message }}</p>
                @endforeach
            </div>
        </div>
    @endif

    @if ($showcases->isNotEmpty())
        <section id="showcase-wrap" class="relative overflow-hidden bg-gray-900">
            <div id="showcase-track" class="flex transition-transform duration-700 ease-out">
                @foreach ($showcases as $showcase)
                    <div class="min-w-full showcase-slide" data-animation="{{ $showcase->animation ?? 'NONE' }}">
                        @if ($showcase->desktop_image || $showcase->image)
                            <div class="relative h-[420px] md:h-[520px]">
                                <img src="{{ route('showcase.image', [$showcase, $showcase->desktop_image ? 'desktop_image' : 'image']) }}?w=1920" alt="{{ $showcase->title ?? 'Showcase' }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40"></div>
                                <div class="showcase-content relative max-w-7xl mx-auto px-4 sm:px-6 py-20 md:py-28 text-center text-white">
                                    <h1 class="text-4xl md:text-5xl font-bold leading-tight">{{ $showcase->title ?? 'Professional Device Services' }}</h1>
                                    @if ($showcase->subtitle)
                                        <p class="mt-4 text-lg text-gray-200 max-w-2xl mx-auto">{{ $showcase->subtitle }}</p>
                                    @endif
                                    @php $href = $showcase->link_type === 'service' && $showcase->service ? route('services.show', $showcase->service->slug) : ($showcase->link_type === 'url' ? $showcase->link_url : route('services.index')); @endphp
                                    <a href="{{ $href }}"
                                       class="inline-block mt-8 bg-accent-600 hover:bg-accent-500 text-white font-medium px-6 py-3 rounded-md">Explore services</a>
                                </div>
                            </div>
                        @else
                            <div class="showcase-content max-w-7xl mx-auto px-4 sm:px-6 py-20 md:py-28 text-center text-white">
                                <h1 class="text-4xl md:text-5xl font-bold leading-tight">{{ $showcase->title ?? 'Professional Device Services' }}</h1>
                                @if ($showcase->subtitle)
                                    <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">{{ $showcase->subtitle }}</p>
                                @endif
                                @php $href = $showcase->link_type === 'service' && $showcase->service ? route('services.show', $showcase->service->slug) : ($showcase->link_type === 'url' ? $showcase->link_url : route('services.index')); @endphp
                                <a href="{{ $href }}"
                                   class="inline-block mt-8 bg-accent-600 hover:bg-accent-500 text-white font-medium px-6 py-3 rounded-md">Explore services</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div id="showcase-dots" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                @foreach ($showcases as $i => $showcase)
                    <span class="w-2 h-2 rounded-full bg-white/40"></span>
                @endforeach
            </div>
        </section>
    @else
        <section class="bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 md:py-28 text-center">
                <h1 class="text-4xl md:text-5xl font-bold leading-tight">{{ $sections['hero']->title ?? 'Professional Device Services' }}</h1>
                <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">{{ $sections['hero']->content ?? 'Reliable GSM repair, diagnostics and maintenance services with secure order tracking.' }}</p>
                <a href="{{ route('services.index') }}" class="inline-block mt-8 bg-accent-600 hover:bg-accent-500 text-white font-medium px-6 py-3 rounded-md">Explore services</a>
            </div>
        </section>
    @endif

    @php $stats = is_array($sections['stats']->content ?? null) ? $sections['stats']->content : []; @endphp
    @if (count($stats))
        <section class="bg-gray-50 border-y border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                    @foreach ($stats as $stat)
                        <div>
                            <div class="text-3xl font-bold text-blue-600">{{ $stat['value'] ?? '' }}</div>
                            <div class="text-sm text-gray-600 mt-1">{{ $stat['label'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($categories->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Browse by category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($categories as $category)
                    <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                       class="bg-white border border-gray-200 rounded-lg p-5 hover:border-blue-400 hover:shadow-sm transition">
                        <div class="text-3xl mb-2">{{ $category->icon ?? '📦' }}</div>
                        <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ $category->services_count ?? '' }} {{ Str::plural('service', $category->services_count ?? 0) }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($featuredServices->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6 pb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured services</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($featuredServices as $service)
                    <a href="{{ route('services.show', $service->slug) }}"
                       class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-blue-400 hover:shadow-sm transition flex flex-col">
                        @if ($service->image)
                            <img src="{{ route('services.image', $service) }}?w=480" alt="{{ $service->name }}" class="w-full h-36 object-cover">
                        @endif
                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl">{{ $service->icon ?? '🔧' }}</span>
                                @if ($service->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Online</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Offline</span>
                                @endif
                            </div>
                            <h3 class="font-semibold text-gray-900">{{ $service->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1 flex-1">{{ $service->short_description }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="font-bold text-gray-900">
                                    @if ($service->service_type === 'FREE')
                                        Free
                                    @elseif ($service->service_type === 'EXTERNAL')
                                        External
                                    @else
                                        {{ $service->currency }} {{ number_format((float) $service->price, 2) }}
                                    @endif
                                </span>
                                <span class="text-blue-600 text-sm font-medium">View →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @php $steps = is_array($sections['how_it_works']->content ?? null) ? $sections['how_it_works']->content : []; @endphp
    @if (count($steps))
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ $sections['how_it_works']->title ?? 'How it works' }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach ($steps as $i => $step)
                    <div class="text-center">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center mx-auto">{{ $i + 1 }}</div>
                        <h3 class="font-semibold mt-3">{{ $step['title'] ?? '' }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $step['text'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($faqs->isNotEmpty())
        <section class="max-w-4xl mx-auto px-4 sm:px-6 py-6 pb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Frequently asked questions</h2>
            <div class="space-y-3">
                @foreach ($faqs as $faq)
                    <details class="bg-white border border-gray-200 rounded-lg p-4">
                        <summary class="font-medium text-gray-900 cursor-pointer">{{ $faq->question }}</summary>
                        <p class="text-sm text-gray-600 mt-2">{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 text-center text-white">
            <h2 class="text-2xl md:text-3xl font-bold">{{ $sections['cta']->title ?? 'Ready to get started?' }}</h2>
            <p class="mt-2 text-blue-100">{{ $sections['cta']->content ?? 'Create an account or place a guest order in minutes.' }}</p>
            <div class="mt-6 flex justify-center gap-4">
                <a href="{{ route('services.index') }}" class="bg-white text-blue-600 font-medium px-6 py-3 rounded-md hover:bg-blue-50">Browse services</a>
                <a href="{{ route('register') }}" class="border border-white text-white font-medium px-6 py-3 rounded-md hover:bg-blue-700">Create account</a>
            </div>
        </div>
    </section>
@endsection
