<div>
    <section class="mx-auto max-w-6xl px-5 py-14">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">{{ ucfirst($taxonomyType) }}</p>
        <h1 class="mt-3 text-4xl font-bold tracking-normal text-slate-950">
            {{ $taxonomy->name }}
        </h1>
        @if (filled($taxonomy->description))
            <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $taxonomy->description }}</p>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-5 pb-14">
        @if ($posts->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-slate-600">
                No published posts are available here yet.
            </div>
        @else
            <div class="grid gap-6">
                @foreach ($posts as $post)
                    @include('themes.default.partials.post-card', ['post' => $post, 'currentLocale' => $currentLocale])
                @endforeach
            </div>

            <nav class="mt-8" aria-label="Pagination">
                {{ $posts->links() }}
            </nav>
        @endif
    </section>
</div>
