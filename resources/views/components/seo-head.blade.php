@props(['seo'])

<title>{{ $seo->title }}</title>
@if (filled($seo->description))
    <meta name="description" content="{{ $seo->description }}" />
@endif
<link rel="canonical" href="{{ $seo->canonicalUrl }}" />
<meta name="robots" content="{{ $seo->robots }}" />

<meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}" />
<meta property="og:title" content="{{ $seo->ogTitle }}" />
@if (filled($seo->ogDescription))
    <meta property="og:description" content="{{ $seo->ogDescription }}" />
@endif
<meta property="og:url" content="{{ $seo->canonicalUrl }}" />
<meta property="og:type" content="{{ ($seo->structuredData['@type'] ?? null) === 'BlogPosting' ? 'article' : 'website' }}" />
@if (filled($seo->ogImage))
    <meta property="og:image" content="{{ $seo->ogImage }}" />
@endif

<meta name="twitter:card" content="{{ filled($seo->ogImage) ? 'summary_large_image' : 'summary' }}" />
<meta name="twitter:title" content="{{ $seo->ogTitle }}" />
@if (filled($seo->ogDescription))
    <meta name="twitter:description" content="{{ $seo->ogDescription }}" />
@endif

@foreach ($seo->alternates as $alternate)
    <link rel="alternate" hreflang="{{ $alternate['locale'] }}" href="{{ $alternate['url'] }}" />
@endforeach
@if (filled($seo->xDefaultUrl))
    <link rel="alternate" hreflang="x-default" href="{{ $seo->xDefaultUrl }}" />
@endif

@if ($seo->structuredData !== null)
    <script type="application/ld+json">{!! app(\App\Support\Seo\SeoManager::class)->jsonLd($seo->structuredData) !!}</script>
@endif
