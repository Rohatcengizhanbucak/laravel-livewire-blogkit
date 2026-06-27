<div>
    <article class="mx-auto max-w-3xl px-5 py-14">
        <nav class="mb-8 text-sm text-slate-600" aria-label="{{ trans('blog.aria.breadcrumb', [], $currentLocale->code) }}">
            <a href="{{ route('blog.index', ['locale' => $currentLocale->code]) }}" class="font-medium hover:text-slate-950">{{ trans('blog.nav.blog', [], $currentLocale->code) }}</a>
            @if ($post->category)
                <span aria-hidden="true">/</span>
                <a href="{{ route('blog.categories.show', ['locale' => $currentLocale->code, 'slug' => $post->category->slug]) }}" class="font-medium hover:text-slate-950">{{ $post->category->name }}</a>
            @endif
        </nav>

        <header>
            <div class="flex flex-wrap items-center gap-3 text-xs font-medium uppercase tracking-wide text-slate-500">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale($currentLocale->code)->isoFormat(trans('blog.date_format', [], $currentLocale->code)) }}</time>
                @endif
                <span>{{ trans_choice('blog.reading_time', $post->reading_time, ['minutes' => $post->reading_time], $currentLocale->code) }}</span>
                @if ($post->author)
                    <span>{{ trans('blog.by_author', ['author' => $post->author->name], $currentLocale->code) }}</span>
                @endif
            </div>

            <h1 class="mt-4 text-4xl font-bold tracking-normal text-slate-950 md:text-5xl">
                {{ $translation->title }}
            </h1>

            @if (filled($translation->excerpt))
                <p class="mt-5 text-xl leading-8 text-slate-600">{{ $translation->excerpt }}</p>
            @endif

            @if (filled($post->cover_image))
                <img
                    src="{{ $post->cover_image }}"
                    alt="{{ $translation->title }}"
                    width="1200"
                    height="630"
                    fetchpriority="high"
                    class="mt-8 aspect-[1200/630] w-full rounded-lg object-cover"
                >
            @endif
        </header>

        <div class="prose prose-slate mt-10 max-w-none text-slate-800">
            {!! nl2br(e($translation->content ?? '')) !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <nav class="mt-10 flex flex-wrap gap-2" aria-label="{{ trans('blog.aria.post_tags', [], $currentLocale->code) }}">
                @foreach ($post->tags as $tag)
                    <a href="{{ route('blog.tags.show', ['locale' => $currentLocale->code, 'slug' => $tag->slug]) }}" class="rounded-full border border-slate-200 px-3 py-1 text-sm font-medium text-slate-600 hover:border-slate-400 hover:text-slate-950">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </nav>
        @endif
    </article>

    @if ($relatedPosts->isNotEmpty())
        <aside class="mx-auto max-w-6xl px-5 pb-14" aria-labelledby="related-posts-heading">
            <h2 id="related-posts-heading" class="mb-5 text-2xl font-bold tracking-normal text-slate-950">{{ trans('blog.related_posts', [], $currentLocale->code) }}</h2>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($relatedPosts as $relatedPost)
                    @include('themes.default.partials.post-card', ['post' => $relatedPost, 'currentLocale' => $currentLocale])
                @endforeach
            </div>
        </aside>
    @endif
</div>
