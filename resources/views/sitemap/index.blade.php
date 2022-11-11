<?= '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ $lastHomePageUpdate }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.00</priority>
    </url>
    <url>
        <loc>{{ route('login') }}</loc>
        <lastmod>{{ $lastHomePageUpdate }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.10</priority>
    </url>
    <url>
        <loc>{{ route('register') }}</loc>
        <lastmod>{{ $lastHomePageUpdate }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.10</priority>
    </url>
    <url>
        <loc>{{ route('privacy') }}</loc>
        <lastmod>{{ $lastHomePageUpdate }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.10</priority>
    </url>
    <url>
        <loc>{{ route('password.request') }}</loc>
        <lastmod>{{ $lastHomePageUpdate }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.10</priority>
    </url>
    @foreach($courses as $course)
        <url>
            <loc>{{ route('course.show', ['course' => $course, 'slug' => $course->slug]) }}</loc>
            <lastmod>{{ $course->updated_at->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>{!! $controller->calculatePriority($course) !!}</priority>
        </url>
    @endforeach
</urlset>
