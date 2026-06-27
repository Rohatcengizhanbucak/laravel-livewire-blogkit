<?php

namespace App\Support\Localization;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Models\Tag;

class LocaleUrlFactory
{
    /**
     * @param  iterable<int, Locale>  $locales
     * @return array<string, string>
     */
    public function blogIndex(iterable $locales): array
    {
        $urls = [];

        foreach ($locales as $locale) {
            $urls[$locale->code] = route('blog.index', ['locale' => $locale->code]);
        }

        return $urls;
    }

    /**
     * @param  iterable<int, Locale>  $locales
     * @return array<string, string>
     */
    public function search(iterable $locales, string $query = ''): array
    {
        $urls = [];
        $params = filled($query) ? ['q' => $query] : [];

        foreach ($locales as $locale) {
            $urls[$locale->code] = route('blog.search', [
                'locale' => $locale->code,
                ...$params,
            ]);
        }

        return $urls;
    }

    /**
     * @param  iterable<int, Locale>  $locales
     * @return array<string, string>
     */
    public function category(Category $category, iterable $locales): array
    {
        $urls = [];

        foreach ($locales as $locale) {
            $urls[$locale->code] = route('blog.categories.show', [
                'locale' => $locale->code,
                'slug' => $category->slug,
            ]);
        }

        return $urls;
    }

    /**
     * @param  iterable<int, Locale>  $locales
     * @return array<string, string>
     */
    public function tag(Tag $tag, iterable $locales): array
    {
        $urls = [];

        foreach ($locales as $locale) {
            $urls[$locale->code] = route('blog.tags.show', [
                'locale' => $locale->code,
                'slug' => $tag->slug,
            ]);
        }

        return $urls;
    }

    /** @return array<string, string> */
    public function post(Post $post): array
    {
        $urls = [];

        foreach ($post->translations as $translation) {
            if ($translation->locale?->is_active !== true) {
                continue;
            }

            $urls[$translation->locale->code] = route('blog.show', [
                'locale' => $translation->locale->code,
                'slug' => $translation->slug,
            ]);
        }

        return $urls;
    }
}
