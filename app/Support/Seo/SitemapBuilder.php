<?php

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Models\Tag;
use App\Support\Localization\LocaleResolver;
use Illuminate\Support\HtmlString;

class SitemapBuilder
{
    public function __construct(
        private readonly LocaleResolver $locales,
    ) {}

    public function render(): string
    {
        $urls = [];

        foreach ($this->locales->active() as $locale) {
            $urls[] = $this->entry(route('blog.index', ['locale' => $locale->code]));
        }

        Post::query()
            ->published()
            ->where('allow_index', true)
            ->with(['translations.locale', 'seoMetas'])
            ->get()
            ->each(function (Post $post) use (&$urls): void {
                foreach ($post->translations as $translation) {
                    if (! $translation->locale?->is_active || ! $this->isIndexable($post, $translation->locale)) {
                        continue;
                    }

                    $urls[] = $this->entry(
                        route('blog.show', ['locale' => $translation->locale->code, 'slug' => $translation->slug]),
                        $post->updated_at?->toDateString(),
                    );
                }
            });

        Category::query()
            ->active()
            ->get()
            ->each(function (Category $category) use (&$urls): void {
                foreach ($this->locales->active() as $locale) {
                    if (! $this->taxonomyHasPublicPosts($category, $locale)) {
                        continue;
                    }

                    $urls[] = $this->entry(route('blog.categories.show', ['locale' => $locale->code, 'slug' => $category->slug]));
                }
            });

        Tag::query()
            ->whereNotNull('description')
            ->get()
            ->each(function (Tag $tag) use (&$urls): void {
                foreach ($this->locales->active() as $locale) {
                    if ($this->taxonomyPublicPostCount($tag, $locale) < 2) {
                        continue;
                    }

                    $urls[] = $this->entry(route('blog.tags.show', ['locale' => $locale->code, 'slug' => $tag->slug]));
                }
            });

        return view('seo.sitemap', ['urls' => $urls])->render();
    }

    private function isIndexable(Post $post, Locale $locale): bool
    {
        $meta = $post->seoMetas->firstWhere('locale_id', $locale->id)
            ?? $post->seoMetas->firstWhere('locale_id', null);

        return $meta === null || $meta->robots_index;
    }

    private function taxonomyHasPublicPosts(Category $category, Locale $locale): bool
    {
        return $this->taxonomyPublicPostCount($category, $locale) > 0;
    }

    private function taxonomyPublicPostCount(Category|Tag $taxonomy, Locale $locale): int
    {
        $query = Post::query()
            ->published()
            ->where('allow_index', true)
            ->whereHas('translations', fn ($query) => $query->where('locale_id', $locale->id));

        if ($taxonomy instanceof Category) {
            $query->where('category_id', $taxonomy->id);
        } else {
            $query->whereHas('tags', fn ($query) => $query->whereKey($taxonomy->id));
        }

        return $query->count();
    }

    /** @return array{loc: HtmlString, lastmod: string|null} */
    private function entry(string $loc, ?string $lastmod = null): array
    {
        return [
            'loc' => new HtmlString(e($loc)),
            'lastmod' => $lastmod,
        ];
    }
}
