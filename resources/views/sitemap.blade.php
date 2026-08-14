<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ url('/') }}</loc></url>
    <url><loc>{{ route('services.index') }}</loc></url>
    <url><loc>{{ route('faq') }}</loc></url>
    <url><loc>{{ route('how-it-works') }}</loc></url>
    <url><loc>{{ route('contact') }}</loc></url>
    <url><loc>{{ route('announcements') }}</loc></url>
    @foreach ($services as $service)
        <url><loc>{{ route('services.show', $service->slug) }}</loc></url>
    @endforeach
    @foreach (['terms', 'privacy', 'refunds', 'acceptable-use'] as $page)
        <url><loc>{{ route('page', $page) }}</loc></url>
    @endforeach
</urlset>