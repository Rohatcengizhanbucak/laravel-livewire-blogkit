@php
    $translation = $post->translations->first();
@endphp

@if ($translation)
    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center gap-3 text-xs font-medium uppercase tracking-wide text-slate-500">
            @if ($post->category)
                <a href="{{ route('blog.categories.show', ['locale' => $currentLocale->code, 'slug' => $post->category->slug]) }}" class="text-slate-700 hover:text-slate-950">
                    {{ $post->category->name }}
                </a>
            @endif
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale($currentLocale->code)->isoFormat(trans('blog.date_format', [], $currentLocale->code)) }}</time>
            @endif
            <span>{{ trans_choice('blog.reading_time', $post->reading_time, ['minutes' => $post->reading_time], $currentLocale->code) }}</span>
        </div>

        <h2 class="text-2xl font-bold tracking-normal text-slate-950">
            <a href="{{ route('blog.show', ['locale' => $currentLocale->code, 'slug' => $translation->slug]) }}" class="hover:text-slate-700">
                {{ $translation->title }}
            </a>
        </h2>

        @if (filled($translation->excerpt))
            <p class="mt-3 text-base leading-7 text-slate-600">{{ $translation->excerpt }}</p>
        @endif

        @if ($post->tags->isNotEmpty())
            <nav class="mt-5 flex flex-wrap gap-2" aria-label="{{ trans('blog.aria.post_tags', [], $currentLocale->code) }}">
                @foreach ($post->tags as $tag)
                    <a href="{{ route('blog.tags.show', ['locale' => $currentLocale->code, 'slug' => $tag->slug]) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600 hover:border-slate-400 hover:text-slate-950">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </nav>
        @endif
    </article>
@endif
