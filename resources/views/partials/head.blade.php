<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@if (isset($seo) && $seo instanceof \App\Support\Seo\SeoData)
    <x-seo-head :seo="$seo" />
@else
    <title>
        {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>
    <meta name="robots" content="noindex,nofollow" />
@endif

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@if ($useWebFonts ?? true)
    @fonts
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
