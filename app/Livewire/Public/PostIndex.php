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
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class PostIndex extends Component
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
        $page = max(1, (int) request()->query('page', 1));
        $posts = $this->posts($locale, $page);

        abort_if($page > 1 && $posts->count() === 0, 404);

        $theme = $themes->default();
        $seoData = $seo->blogIndex($locale, $page);

        return $views->make(
            theme: $theme,
            view: 'posts.index',
            data: [
                'currentLocale' => $locale,
                'posts' => $posts,
                'seo' => $seoData,
                'theme' => $theme,
            ],
            layoutData: [
                'currentLocale' => $locale,
                'localeUrls' => $localeUrls->blogIndex($activeLocales),
                'locales' => $activeLocales,
                'seo' => $seoData,
                'theme' => $theme,
            ],
        );
    }

    /** @return LengthAwarePaginator<int, Post> */
    private function posts(Locale $locale, int $page): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->forLocale($locale)
            ->withPublicRelations($locale)
            ->orderByDesc('pinned_at')
            ->orderByDesc('published_at')
            ->paginate(perPage: 10, page: $page);
    }
}
