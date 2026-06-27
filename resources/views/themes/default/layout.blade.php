<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale->code ?? app()->getLocale()) }}" dir="{{ $currentLocale->direction ?? 'ltr' }}">
    <head>
        @include('partials.head', ['seo' => $seo ?? null, 'useWebFonts' => false])
    </head>
    <body
        class="blog-public-surface min-h-screen bg-slate-50 text-slate-950 antialiased"
        @if (filled($theme?->cssVariables ?? null)) style="{{ $theme->cssVariables }}" @endif
    >
        <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-slate-950 focus:shadow">
            {{ trans('blog.nav.skip_to_content', [], $currentLocale->code) }}
        </a>

        <header class="border-b border-slate-200 bg-white/95">
            <nav class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-5 py-4" aria-label="{{ trans('blog.aria.primary_navigation', [], $currentLocale->code) }}">
                <a href="{{ route('blog.index', ['locale' => $currentLocale->code]) }}" class="font-serif text-3xl font-bold leading-none tracking-normal text-slate-950 transition hover:text-slate-700">
                    {{ config('app.name', 'BlogKit') }}
                </a>

                <form
                    action="{{ route('blog.search', ['locale' => $currentLocale->code]) }}"
                    method="GET"
                    role="search"
                    class="order-3 flex w-full items-center rounded-full bg-slate-100 text-slate-700 ring-1 ring-transparent transition focus-within:bg-white focus-within:ring-slate-300 md:order-none md:w-72"
                >
                    <label for="public-search" class="sr-only">
                        {{ trans('blog.nav.search', [], $currentLocale->code) }}
                    </label>
                    <svg class="ml-4 h-4 w-4 flex-none text-slate-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input
                        id="public-search"
                        name="q"
                        type="search"
                        value="{{ request()->query('q', '') }}"
                        placeholder="{{ trans('blog.nav.search_placeholder', [], $currentLocale->code) }}"
                        class="min-w-0 flex-1 bg-transparent px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-500 focus:outline-none"
                    >
                    <button type="submit" class="mr-1 inline-flex h-8 w-8 flex-none items-center justify-center rounded-full text-slate-500 transition hover:bg-white hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-300" aria-label="{{ trans('blog.nav.search', [], $currentLocale->code) }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </form>

                <div class="ml-auto flex flex-wrap items-center justify-end gap-4 text-sm">
                    <a href="{{ route('blog.index', ['locale' => $currentLocale->code]) }}" class="font-medium text-slate-700 transition hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-4">
                        {{ trans('blog.nav.blog', [], $currentLocale->code) }}
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-medium text-slate-700 transition hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-4">
                            {{ trans('blog.nav.dashboard', [], $currentLocale->code) }}
                        </a>
                    @else
                        @if (\Illuminate\Support\Facades\Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center rounded-full bg-emerald-700 px-4 py-2 font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                                {{ trans('blog.nav.sign_up', [], $currentLocale->code) }}
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="font-medium text-slate-700 transition hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-4">
                            {{ trans('blog.nav.sign_in', [], $currentLocale->code) }}
                        </a>
                    @endauth

                    @if (($locales ?? collect())->count() > 1)
                        <div class="flex items-center rounded-full bg-slate-100 p-1 text-xs font-semibold uppercase tracking-wide text-slate-500" aria-label="{{ trans('blog.nav.language_switcher', [], $currentLocale->code) }}">
                            @foreach ($locales as $availableLocale)
                                @php
                                    $availableLocaleUrl = ($localeUrls ?? [])[$availableLocale->code] ?? null;
                                    $isCurrentLocale = $availableLocale->code === $currentLocale->code;
                                @endphp

                                @if ($availableLocaleUrl)
                                    <a
                                        href="{{ $availableLocaleUrl }}"
                                        hreflang="{{ $availableLocale->code }}"
                                        @if ($isCurrentLocale) aria-current="page" @endif
                                        class="rounded-full px-2.5 py-1 transition {{ $isCurrentLocale ? 'bg-white text-slate-950 shadow-sm' : 'hover:bg-white/80 hover:text-slate-800' }}"
                                    >
                                        {{ $availableLocale->code }}
                                    </a>
                                @else
                                    <span
                                        aria-disabled="true"
                                        title="{{ trans('blog.nav.translation_unavailable', [], $currentLocale->code) }}"
                                        class="cursor-not-allowed rounded-full px-2.5 py-1 text-slate-300"
                                    >
                                        {{ $availableLocale->code }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </nav>
        </header>

        <main id="content">
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-8 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                <p>&copy; {{ now()->year }} {{ config('app.name', 'BlogKit') }}. {{ trans('blog.footer.tagline', [], $currentLocale->code) }}</p>
                <a href="{{ route('sitemap') }}" class="font-medium text-slate-700 hover:text-slate-950">{{ trans('blog.nav.sitemap', [], $currentLocale->code) }}</a>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
