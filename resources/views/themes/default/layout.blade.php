<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale->code ?? app()->getLocale()) }}" dir="{{ $currentLocale->direction ?? 'ltr' }}">
    <head>
        @include('partials.head', ['seo' => $seo ?? null, 'useWebFonts' => false])
    </head>
    <body
        class="blog-public-surface flex min-h-screen flex-col bg-white text-neutral-950 antialiased"
        @if (filled($theme?->cssVariables ?? null)) style="{{ $theme->cssVariables }}" @endif
    >
        <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-neutral-950 focus:shadow">
            {{ trans('blog.nav.skip_to_content', [], $currentLocale->code) }}
        </a>

        <header class="sticky top-0 z-40 border-b border-neutral-200/80 bg-white/90 shadow-[0_1px_0_rgba(10,10,10,0.03)] backdrop-blur-xl supports-[backdrop-filter]:bg-white/80">
            <nav class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-5 py-4" aria-label="{{ trans('blog.aria.primary_navigation', [], $currentLocale->code) }}">
                <a href="{{ route('blog.index', ['locale' => $currentLocale->code]) }}" class="font-serif text-3xl font-bold leading-none tracking-normal text-neutral-950 transition hover:text-neutral-700">
                    {{ config('app.name', 'BlogKit') }}
                </a>

                <form
                    action="{{ route('blog.search', ['locale' => $currentLocale->code]) }}"
                    method="GET"
                    role="search"
                    class="order-3 flex w-full items-center rounded-full bg-neutral-100 text-neutral-700 ring-1 ring-transparent transition focus-within:bg-white focus-within:ring-neutral-300 md:order-none md:w-72"
                >
                    <label for="public-search" class="sr-only">
                        {{ trans('blog.nav.search', [], $currentLocale->code) }}
                    </label>
                    <svg class="ml-4 h-4 w-4 flex-none text-neutral-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input
                        id="public-search"
                        name="q"
                        type="search"
                        value="{{ request()->query('q', '') }}"
                        placeholder="{{ trans('blog.nav.search_placeholder', [], $currentLocale->code) }}"
                        class="min-w-0 flex-1 bg-transparent px-3 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-500 focus:outline-none"
                    >
                    <input type="hidden" name="type" value="stories">
                    <button type="submit" class="mr-1 inline-flex h-8 w-8 flex-none items-center justify-center rounded-full text-neutral-500 transition hover:bg-white hover:text-neutral-950 focus:outline-none focus:ring-2 focus:ring-neutral-300" aria-label="{{ trans('blog.nav.search', [], $currentLocale->code) }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </form>

                @if (($locales ?? collect())->count() > 1)
                    <details class="group relative order-4 flex flex-none items-center md:order-none">
                        <summary
                            class="flex h-10 min-w-18 cursor-pointer list-none items-center justify-center gap-2 rounded-full bg-neutral-100 px-4 text-sm font-semibold uppercase tracking-wide text-neutral-800 ring-1 ring-transparent transition hover:bg-white hover:ring-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300 group-open:bg-white group-open:ring-neutral-300 [&::-webkit-details-marker]:hidden"
                            aria-label="{{ trans('blog.nav.language_switcher', [], $currentLocale->code) }}"
                        >
                            <span>{{ strtoupper($currentLocale->code) }}</span>
                            <svg class="h-4 w-4 text-neutral-500 transition group-open:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="m5.5 7.5 4.5 4.5 4.5-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </summary>

                        <div class="absolute left-1/2 top-full z-50 mt-2 w-36 -translate-x-1/2 rounded-lg border border-neutral-200 bg-white p-1 shadow-lg shadow-neutral-950/10 ring-1 ring-neutral-950/5">
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
                                        class="flex items-center justify-between rounded-md px-3 py-2 text-sm font-semibold uppercase tracking-wide transition {{ $isCurrentLocale ? 'bg-neutral-100 text-neutral-950' : 'text-neutral-700 hover:bg-neutral-100 hover:text-neutral-950' }}"
                                    >
                                        <span>{{ strtoupper($availableLocale->code) }}</span>
                                        @if ($isCurrentLocale)
                                            <span class="h-1.5 w-1.5 rounded-full bg-neutral-950" aria-hidden="true"></span>
                                        @endif
                                    </a>
                                @else
                                    <span
                                        aria-disabled="true"
                                        title="{{ trans('blog.nav.translation_unavailable', [], $currentLocale->code) }}"
                                        class="flex cursor-not-allowed items-center justify-between rounded-md px-3 py-2 text-sm font-semibold uppercase tracking-wide text-neutral-300"
                                    >
                                        <span>{{ strtoupper($availableLocale->code) }}</span>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @endif

                <div class="ml-auto flex flex-wrap items-center justify-end gap-4 text-sm">
                    <a href="{{ route('blog.index', ['locale' => $currentLocale->code]) }}" class="font-medium text-neutral-700 transition hover:text-neutral-950 focus:outline-none focus:ring-2 focus:ring-neutral-300 focus:ring-offset-4">
                        {{ trans('blog.nav.blog', [], $currentLocale->code) }}
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-medium text-neutral-700 transition hover:text-neutral-950 focus:outline-none focus:ring-2 focus:ring-neutral-300 focus:ring-offset-4">
                            {{ trans('blog.nav.dashboard', [], $currentLocale->code) }}
                        </a>
                    @else
                        @if (\Illuminate\Support\Facades\Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center rounded-full bg-emerald-700 px-4 py-2 font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                                {{ trans('blog.nav.sign_up', [], $currentLocale->code) }}
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="font-medium text-neutral-700 transition hover:text-neutral-950 focus:outline-none focus:ring-2 focus:ring-neutral-300 focus:ring-offset-4">
                            {{ trans('blog.nav.sign_in', [], $currentLocale->code) }}
                        </a>
                    @endauth
                </div>
            </nav>
        </header>

        <main id="content" class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-neutral-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-8 text-sm text-neutral-600 md:flex-row md:items-center md:justify-between">
                <p>&copy; {{ now()->year }} {{ config('app.name', 'BlogKit') }}. {{ trans('blog.footer.tagline', [], $currentLocale->code) }}</p>
                <a href="{{ route('sitemap') }}" class="font-medium text-neutral-700 hover:text-neutral-950">{{ trans('blog.nav.sitemap', [], $currentLocale->code) }}</a>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
