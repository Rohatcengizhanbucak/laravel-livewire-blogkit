<div>
    <section class="min-h-[25.25rem] bg-white">
        <div class="mx-auto max-w-6xl px-5 py-14">
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-600">{{ trans('blog.index.eyebrow', [], $currentLocale->code) }}</p>
            <h1 class="mt-3 max-w-3xl text-4xl font-bold tracking-normal text-slate-950 md:text-5xl">
                {{ trans('blog.index.heading', [], $currentLocale->code) }}
            </h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                {{ trans('blog.index.description', [], $currentLocale->code) }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-5 py-10">
        @if ($posts->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-slate-600">
                {{ trans('blog.index.empty', [], $currentLocale->code) }}
            </div>
        @else
            <div class="grid gap-6">
                @foreach ($posts as $post)
                    @include('themes.default.partials.post-card', ['post' => $post, 'currentLocale' => $currentLocale])
                @endforeach
            </div>

            <nav class="mt-8" aria-label="{{ trans('blog.aria.pagination', [], $currentLocale->code) }}">
                {{ $posts->links() }}
            </nav>
        @endif
    </section>
</div>
