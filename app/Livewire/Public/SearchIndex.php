<?php

namespace App\Livewire\Public;

use App\Models\Locale;
use App\Models\Post;
use App\Support\Localization\LocaleResolver;
use App\Support\Localization\LocaleUrlFactory;
use App\Support\Seo\SeoManager;
use App\Support\Theming\ThemedPageViewFactory;
use App\Support\Theming\ThemeManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class SearchIndex extends Component
{
    public string $locale;

    public function mount(string $locale): void
    {
        $this->locale = $locale;
    }

    public function render(LocaleResolver $locales, SeoManager $seo, ThemeManager $themes, ThemedPageViewFactory $views, LocaleUrlFactory $localeUrls): View
    {
        $locale = $locales->resolve($this->locale);
        $activeLocales = $locales->active();
        $query = $this->query();
        $page = max(1, (int) request()->query('page', 1));
        $posts = $this->posts($locale, $query, $page);
        $theme = $themes->default();
        $seoData = $seo->search($locale, $query);

        return $views->make(
            theme: $theme,
            view: 'search.index',
            data: [
                'currentLocale' => $locale,
                'posts' => $posts,
                'query' => $query,
                'seo' => $seoData,
                'theme' => $theme,
            ],
            layoutData: [
                'currentLocale' => $locale,
                'localeUrls' => $localeUrls->search($activeLocales, $query),
                'locales' => $activeLocales,
                'seo' => $seoData,
                'theme' => $theme,
            ],
        );
    }

    private function query(): string
    {
        return trim((string) request()->query('q', ''));
    }

    /** @return LengthAwarePaginator<int, Post> */
    private function posts(Locale $locale, string $query, int $page): LengthAwarePaginator
    {
        if (mb_strlen($query) < 2) {
            return new LengthAwarePaginator([], 0, 10, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query).'%';

        return Post::query()
            ->published()
            ->forLocale($locale)
            ->withPublicRelations($locale)
            ->whereHas('translations', function (Builder $builder) use ($locale, $like): void {
                $builder
                    ->where('locale_id', $locale->id)
                    ->where(function (Builder $builder) use ($like): void {
                        $builder
                            ->where('title', 'like', $like)
                            ->orWhere('excerpt', 'like', $like)
                            ->orWhere('content', 'like', $like);
                    });
            })
            ->orderByDesc('pinned_at')
            ->orderByDesc('published_at')
            ->paginate(perPage: 10, page: $page)
            ->withQueryString();
    }
}
