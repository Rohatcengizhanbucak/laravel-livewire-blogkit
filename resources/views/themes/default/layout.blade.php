<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale->code ?? app()->getLocale()) }}" dir="{{ $currentLocale->direction ?? 'ltr' }}">
    <head>
        @include('partials.head', ['seo' => $seo ?? null])
    </head>
    <body
        class="min-h-screen bg-slate-50 text-slate-950 antialiased"
        @if (filled($theme?->cssVariables ?? null)) style="{{ $theme->cssVariables }}" @endif
    >
        <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-slate-950 focus:shadow">
            Skip to content
        </a>

        <header class="border-b border-slate-200 bg-white/95">
            <nav class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-5 py-4" aria-label="Primary navigation">
                <a href="{{ route('blog.index', ['locale' => $currentLocale->code]) }}" class="text-base font-bold text-slate-950">
                    {{ config('app.name', 'BlogKit') }}
                </a>

                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('blog.index', ['locale' => $currentLocale->code]) }}" class="font-medium text-slate-700 hover:text-slate-950">
                        Blog
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-medium text-slate-700 hover:text-slate-950">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-slate-700 hover:text-slate-950">
                            Log in
                        </a>
                    @endauth
                </div>
            </nav>

            @if (($locales ?? collect())->count() > 1)
                <div class="mx-auto flex max-w-6xl gap-3 px-5 pb-4 text-xs uppercase tracking-wide text-slate-500">
                    @foreach ($locales as $availableLocale)
                        <a
                            href="{{ route('blog.index', ['locale' => $availableLocale->code]) }}"
                            hreflang="{{ $availableLocale->code }}"
                            class="{{ $availableLocale->code === $currentLocale->code ? 'font-bold text-slate-950' : 'hover:text-slate-800' }}"
                        >
                            {{ $availableLocale->code }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        <main id="content">
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-8 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                <p>&copy; {{ now()->year }} {{ config('app.name', 'BlogKit') }}. Open-source Laravel publishing foundation.</p>
                <a href="{{ route('sitemap') }}" class="font-medium text-slate-700 hover:text-slate-950">Sitemap</a>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
