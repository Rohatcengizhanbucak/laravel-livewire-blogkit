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

        <nav class="mt-10 flex gap-8 overflow-x-auto border-b border-neutral-200 text-sm font-medium text-neutral-500" aria-label="{{ trans('blog.search.result_sections', [], $currentLocale->code) }}">
            @foreach ($searchTypes as $searchType)
                @php
                    $isActive = $activeType === $searchType;
                @endphp

                <a
                    href="{{ $tabUrls[$searchType] }}"
                    @if ($isActive) aria-current="page" @endif
                    class="-mb-px inline-flex shrink-0 items-center gap-2 border-b pb-4 transition {{ $isActive ? 'border-neutral-950 text-neutral-950' : 'border-transparent hover:border-neutral-300 hover:text-neutral-800' }}"
                >
                    <span>{{ trans("blog.search.tabs.$searchType", [], $currentLocale->code) }}</span>
                    <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-500">{{ $counts[$searchType] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>
    </section>

    <section class="mx-auto max-w-4xl px-5 pb-16">
        @if (! $isSearchable)
            <div class="border-b border-neutral-200 py-8 text-neutral-600">
                {{ trans('blog.search.short_query', [], $currentLocale->code) }}
            </div>
        @elseif ($results->isEmpty())
            <div class="border-b border-neutral-200 py-8 text-neutral-600">
                {{ trans("blog.search.empty_states.$activeType", ['query' => $query], $currentLocale->code) }}
            </div>
        @elseif ($activeType === 'members')
            <div class="divide-y divide-neutral-200 border-b border-neutral-200">
                @foreach ($results as $member)
                    @php
                        $latestPost = $member->posts->first();
                        $latestTranslation = $latestPost?->translations->first();
                    @endphp

                    <article class="flex gap-4 py-8">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-neutral-100 text-sm font-bold text-neutral-700">
                            {{ $member->initials() }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-2xl font-bold tracking-normal text-neutral-950">
                                {{ $member->name }}
                            </h2>
                            <p class="mt-2 text-sm text-neutral-500">
                                {{ trans_choice('blog.search.member_public_posts', (int) $member->public_posts_count, ['count' => (int) $member->public_posts_count], $currentLocale->code) }}
                            </p>

                            @if ($latestPost && $latestTranslation)
                                <p class="mt-4 text-base leading-7 text-neutral-600">
                                    {{ trans('blog.search.latest_story', [], $currentLocale->code) }}
                                    <a href="{{ route('blog.show', ['locale' => $currentLocale->code, 'slug' => $latestTranslation->slug]) }}" class="font-medium text-neutral-950 hover:text-neutral-700">
                                        {{ $latestTranslation->title }}
                                    </a>
                                    @if ($latestPost->published_at)
                                        <span class="text-neutral-400">/</span>
                                        <time datetime="{{ $latestPost->published_at->toDateString() }}">{{ $latestPost->published_at->locale($currentLocale->code)->isoFormat(trans('blog.date_format', [], $currentLocale->code)) }}</time>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <nav class="mt-8" aria-label="{{ trans('blog.aria.pagination', [], $currentLocale->code) }}">
                {{ $results->links() }}
            </nav>
        @elseif ($activeType === 'tags')
            <div class="divide-y divide-neutral-200 border-b border-neutral-200">
                @foreach ($results as $tag)
                    <article class="py-8">
                        <div class="mb-3 text-sm text-neutral-500">
                            {{ trans_choice('blog.search.tag_public_posts', (int) $tag->public_posts_count, ['count' => (int) $tag->public_posts_count], $currentLocale->code) }}
                        </div>

                        <h2 class="text-2xl font-bold tracking-normal text-neutral-950">
                            <a href="{{ route('blog.tags.show', ['locale' => $currentLocale->code, 'slug' => $tag->slug]) }}" class="hover:text-neutral-700">
                                #{{ $tag->name }}
                            </a>
                        </h2>

                        @if (filled($tag->description))
                            <p class="mt-3 max-w-3xl text-base leading-7 text-neutral-600">{{ $tag->description }}</p>
                        @endif
                    </article>
                @endforeach
            </div>

            <nav class="mt-8" aria-label="{{ trans('blog.aria.pagination', [], $currentLocale->code) }}">
                {{ $results->links() }}
            </nav>
        @else
            <div class="divide-y divide-neutral-200 border-b border-neutral-200">
                @foreach ($results as $post)
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
                {{ $results->links() }}
            </nav>
        @endif
    </section>
</div>
