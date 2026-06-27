<div class="bg-white">
    <section class="mx-auto max-w-4xl px-5 py-14 md:py-16">
        <h1 class="text-4xl font-bold tracking-normal text-neutral-500 md:text-5xl">
            @if (filled($query))
                {{ trans('blog.search.results_for', [], $currentLocale->code) }}
                <span class="text-neutral-950">{{ $query }}</span>
            @else
                <span class="text-neutral-950">{{ trans('blog.search.heading', [], $currentLocale->code) }}</span>
            @endif
        </h1>

        <nav class="mt-10 flex gap-8 border-b border-neutral-200 text-sm font-medium text-neutral-500" aria-label="{{ trans('blog.search.result_sections', [], $currentLocale->code) }}">
            <span class="-mb-px border-b border-neutral-950 pb-4 text-neutral-950">
                {{ trans('blog.search.stories', [], $currentLocale->code) }}
            </span>
        </nav>
    </section>

    <section class="mx-auto max-w-4xl px-5 pb-16">
        @if (mb_strlen($query) < 2)
            <div class="border-b border-neutral-200 py-8 text-neutral-600">
                {{ trans('blog.search.short_query', [], $currentLocale->code) }}
            </div>
        @elseif ($posts->isEmpty())
            <div class="border-b border-neutral-200 py-8 text-neutral-600">
                {{ trans('blog.search.empty', ['query' => $query], $currentLocale->code) }}
            </div>
        @else
            <div class="divide-y divide-neutral-200 border-b border-neutral-200">
                @foreach ($posts as $post)
                    @php
                        $translation = $post->translations->first();
                    @endphp

                    @if ($translation)
                        <article class="py-8">
                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-neutral-500">
                                @if ($post->category)
                                    <a href="{{ route('blog.categories.show', ['locale' => $currentLocale->code, 'slug' => $post->category->slug]) }}" class="font-medium text-neutral-700 hover:text-neutral-950">
                                        {{ $post->category->name }}
                                    </a>
                                    <span aria-hidden="true">&middot;</span>
                                @endif

                                @if ($post->published_at)
                                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale($currentLocale->code)->isoFormat(trans('blog.date_format', [], $currentLocale->code)) }}</time>
                                    <span aria-hidden="true">&middot;</span>
                                @endif

                                <span>{{ trans_choice('blog.reading_time', $post->reading_time, ['minutes' => $post->reading_time], $currentLocale->code) }}</span>
                            </div>

                            <h2 class="max-w-3xl text-2xl font-bold tracking-normal text-neutral-950">
                                <a href="{{ route('blog.show', ['locale' => $currentLocale->code, 'slug' => $translation->slug]) }}" class="hover:text-neutral-700">
                                    {{ $translation->title }}
                                </a>
                            </h2>

                            @if (filled($translation->excerpt))
                                <p class="mt-3 max-w-3xl text-base leading-7 text-neutral-600">{{ $translation->excerpt }}</p>
                            @endif

                            @if ($post->tags->isNotEmpty())
                                <nav class="mt-5 flex flex-wrap gap-2" aria-label="{{ trans('blog.aria.post_tags', [], $currentLocale->code) }}">
                                    @foreach ($post->tags as $tag)
                                        <a href="{{ route('blog.tags.show', ['locale' => $currentLocale->code, 'slug' => $tag->slug]) }}" class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600 hover:bg-neutral-200 hover:text-neutral-950">
                                            #{{ $tag->name }}
                                        </a>
                                    @endforeach
                                </nav>
                            @endif
                        </article>
                    @endif
                @endforeach
            </div>

            <nav class="mt-8" aria-label="{{ trans('blog.aria.pagination', [], $currentLocale->code) }}">
                {{ $posts->links() }}
            </nav>
        @endif
    </section>
</div>
