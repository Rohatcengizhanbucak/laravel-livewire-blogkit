<?php

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\SeoMeta;
use App\Models\Tag;
use App\Support\Localization\LocaleResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SeoManager
{
    public function __construct(
        private readonly LocaleResolver $locales,
    ) {}

    public function blogIndex(Locale $locale, int $page = 1): SeoData
    {
        $title = trans('blog.seo.index.title', [], $locale->code);
        $description = trans('blog.seo.index.description', ['app' => config('app.name', 'BlogKit')], $locale->code);

        return new SeoData(
            title: $this->title($title),
            description: $description,
            canonicalUrl: $this->canonical('blog.index', ['locale' => $locale->code], $page),
            robots: 'index,follow',
            ogTitle: $this->title($title),
            ogDescription: $description,
            ogImage: null,
            alternates: $this->alternateRoutes('blog.index'),
            xDefaultUrl: $this->defaultRoute('blog.index'),
            structuredData: [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $title,
                'description' => $description,
                'url' => $this->canonical('blog.index', ['locale' => $locale->code], $page),
            ],
        );
    }

    public function post(Post $post, PostTranslation $translation, Locale $locale): SeoData
    {
        $meta = $this->metaFor($post, $locale);
        $title = filled($meta?->meta_title) ? (string) $meta->meta_title : $this->title($translation->title);
        $description = filled($meta?->meta_description)
            ? (string) $meta->meta_description
            : $this->excerpt($translation->excerpt ?: $translation->content ?: $translation->title);
        $canonical = $this->canonicalUrl(
            $meta,
            route('blog.show', ['locale' => $locale->code, 'slug' => $translation->slug]),
        );
        $robotsIndex = $meta === null ? true : $meta->robots_index;
        $robotsFollow = $meta === null ? true : $meta->robots_follow;
        $robots = $post->allow_index && $robotsIndex
            ? 'index,'.($robotsFollow ? 'follow' : 'nofollow')
            : 'noindex,follow';
        $ogImage = $meta?->og_image ?: $post->cover_image;

        return new SeoData(
            title: $title,
            description: $description,
            canonicalUrl: $canonical,
            robots: $robots,
            ogTitle: filled($meta?->og_title) ? (string) $meta->og_title : $title,
            ogDescription: filled($meta?->og_description) ? (string) $meta->og_description : $description,
            ogImage: $this->absoluteUrl($ogImage),
            alternates: $this->postAlternates($post),
            xDefaultUrl: $this->defaultPostUrl($post),
            structuredData: $this->postStructuredData($post, $translation, $locale, $canonical, $description),
        );
    }

    public function taxonomy(Category|Tag $taxonomy, Locale $locale, string $type, int $page = 1, int $postCount = 0): SeoData
    {
        $label = $type === 'category'
            ? trans('blog.seo.taxonomy.category', [], $locale->code)
            : trans('blog.seo.taxonomy.tag', [], $locale->code);
        $title = $taxonomy->name.' '.$label;
        $description = $this->excerpt($taxonomy->description ?: trans('blog.seo.taxonomy.description', ['name' => $taxonomy->name], $locale->code));
        $route = $type === 'category' ? 'blog.categories.show' : 'blog.tags.show';
        $robots = ($type === 'category' && $postCount < 1)
            || ($type === 'tag' && ($postCount < 2 || blank($taxonomy->description)))
            ? 'noindex,follow'
            : 'index,follow';

        return new SeoData(
            title: $this->title($title),
            description: $description,
            canonicalUrl: $this->canonical($route, ['locale' => $locale->code, 'slug' => $taxonomy->slug], $page),
            robots: $robots,
            ogTitle: $this->title($title),
            ogDescription: $description,
            ogImage: null,
            alternates: $this->alternateRoutes($route, ['slug' => $taxonomy->slug]),
            xDefaultUrl: $this->defaultRoute($route, ['slug' => $taxonomy->slug]),
            structuredData: [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $title,
                'description' => $description,
                'url' => $this->canonical($route, ['locale' => $locale->code, 'slug' => $taxonomy->slug], $page),
            ],
        );
    }

    public function search(Locale $locale, string $query = ''): SeoData
    {
        $title = trans('blog.seo.search.title', [], $locale->code);
        $description = trans('blog.seo.search.description', ['app' => config('app.name', 'BlogKit')], $locale->code);
        $canonical = route('blog.search', ['locale' => $locale->code]);

        if (filled($query)) {
            $canonical .= '?q='.rawurlencode($query);
        }

        return new SeoData(
            title: $this->title($title),
            description: $description,
            canonicalUrl: $canonical,
            robots: 'noindex,follow',
            ogTitle: $this->title($title),
            ogDescription: $description,
            ogImage: null,
            alternates: $this->searchAlternates($query),
            xDefaultUrl: $this->defaultSearchUrl($query),
            structuredData: null,
        );
    }

    public function noindex(string $title): SeoData
    {
        return new SeoData(
            title: $this->title($title),
            description: '',
            canonicalUrl: url()->current(),
            robots: 'noindex,nofollow',
            ogTitle: $this->title($title),
            ogDescription: '',
            ogImage: null,
        );
    }

    /** @param  array<string, mixed>|null  $data */
    public function jsonLd(?array $data): HtmlString
    {
        if ($data === null) {
            return new HtmlString('');
        }

        return new HtmlString((string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function title(string $title): string
    {
        $site = config('app.name', 'Laravel');

        return Str::of($title)->contains($site) ? $title : $title.' | '.$site;
    }

    private function excerpt(string $value): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?: ''), 160, '');
    }

    /** @param  array<string, mixed>  $params */
    private function canonical(string $route, array $params = [], int $page = 1): string
    {
        $url = route($route, $params);

        return $page > 1 ? $url.'?page='.$page : $url;
    }

    private function canonicalUrl(?SeoMeta $meta, string $fallback): string
    {
        if (blank($meta?->canonical_url)) {
            return $fallback;
        }

        $canonical = (string) $meta->canonical_url;
        $host = parse_url($canonical, PHP_URL_HOST);
        $allowedHost = parse_url(config('app.url'), PHP_URL_HOST);

        if ($host !== null) {
            return $host === $allowedHost ? $canonical : $fallback;
        }

        return url($canonical);
    }

    private function absoluteUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : url($url);
    }

    private function metaFor(Post $post, Locale $locale): ?SeoMeta
    {
        /** @var SeoMeta|null $localeMeta */
        $localeMeta = $post->seoMetas->firstWhere('locale_id', $locale->id);

        /** @var SeoMeta|null $globalMeta */
        $globalMeta = $post->seoMetas->firstWhere('locale_id', null);

        return $localeMeta ?? $globalMeta;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array{locale: string, url: string}>
     */
    private function alternateRoutes(string $route, array $params = []): array
    {
        return $this->locales->active()
            ->map(fn (Locale $locale): array => [
                'locale' => $locale->code,
                'url' => route($route, ['locale' => $locale->code, ...$params]),
            ])
            ->values()
            ->all();
    }

    /** @param  array<string, mixed>  $params */
    private function defaultRoute(string $route, array $params = []): string
    {
        return route($route, ['locale' => $this->locales->default()->code, ...$params]);
    }

    /** @return array<int, array{locale: string, url: string}> */
    private function searchAlternates(string $query): array
    {
        return $this->locales->active()
            ->map(fn (Locale $locale): array => [
                'locale' => $locale->code,
                'url' => route('blog.search', ['locale' => $locale->code])
                    .(filled($query) ? '?q='.rawurlencode($query) : ''),
            ])
            ->values()
            ->all();
    }

    private function defaultSearchUrl(string $query): string
    {
        $url = route('blog.search', ['locale' => $this->locales->default()->code]);

        return filled($query) ? $url.'?q='.rawurlencode($query) : $url;
    }

    /** @return array<int, array{locale: string, url: string}> */
    private function postAlternates(Post $post): array
    {
        return $post->translations
            ->filter(fn (PostTranslation $translation): bool => $translation->locale?->is_active === true)
            ->map(fn (PostTranslation $translation): array => [
                'locale' => $translation->locale->code,
                'url' => route('blog.show', ['locale' => $translation->locale->code, 'slug' => $translation->slug]),
            ])
            ->values()
            ->all();
    }

    private function defaultPostUrl(Post $post): ?string
    {
        $default = $this->locales->default();

        /** @var Collection<int, PostTranslation> $translations */
        $translations = $post->translations;
        $translation = $translations->firstWhere('locale_id', $default->id) ?? $translations->first();

        return $translation instanceof PostTranslation && $translation->locale !== null
            ? route('blog.show', ['locale' => $translation->locale->code, 'slug' => $translation->slug])
            : null;
    }

    /** @return array<string, mixed> */
    private function postStructuredData(Post $post, PostTranslation $translation, Locale $locale, string $canonical, string $description): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $translation->title,
            'description' => $description,
            'url' => $canonical,
            'inLanguage' => $locale->code,
            'datePublished' => $this->atomDate($post->getAttribute('published_at')),
            'dateModified' => $this->atomDate($post->getAttribute('updated_at')),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author === null ? config('app.name', 'Laravel') : $post->author->name,
            ],
            'articleSection' => $post->category?->name,
            'keywords' => $post->tags->pluck('name')->implode(', '),
            'image' => $this->absoluteUrl($post->cover_image),
        ];
    }

    private function atomDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_string($value) && filled($value)) {
            return Carbon::parse($value)->toAtomString();
        }

        return null;
    }
}
