<div>
    <section class="mx-auto max-w-6xl px-5 py-14">
        <p class="text-sm font-semibold uppercase tracking-wide text-neutral-600">{{ trans("blog.taxonomy.$taxonomyType", [], $currentLocale->code) }}</p>
        <h1 class="mt-3 text-4xl font-bold tracking-normal text-neutral-950">
            {{ $taxonomy->name }}
        </h1>
        @if (filled($taxonomy->description))
            <p class="mt-4 max-w-2xl text-lg leading-8 text-neutral-600">{{ $taxonomy->description }}</p>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-5 pb-14">
        @if ($posts->isEmpty())
            <div class="rounded-lg border border-dashed border-neutral-300 bg-white p-8 text-neutral-600">
                {{ trans('blog.taxonomy.empty', [], $currentLocale->code) }}
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
