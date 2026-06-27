<?php

namespace App\Livewire\Public;

use App\Support\Localization\LocaleResolver;
use App\Support\Localization\LocaleUrlFactory;
use App\Support\Search\PublicSearch;
use App\Support\Seo\SeoManager;
use App\Support\Theming\ThemedPageViewFactory;
use App\Support\Theming\ThemeManager;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SearchIndex extends Component
{
    public string $locale;

    public function mount(string $locale): void
    {
        $this->locale = $locale;
    }

    public function render(LocaleResolver $locales, SeoManager $seo, ThemeManager $themes, ThemedPageViewFactory $views, LocaleUrlFactory $localeUrls, PublicSearch $search): View
    {
        $locale = $locales->resolve($this->locale);
        $activeLocales = $locales->active();
        $query = $search->normalizeQuery((string) request()->query('q', ''));
        $activeType = $search->normalizeType((string) request()->query('type', PublicSearch::STORIES));
        $page = max(1, (int) request()->query('page', 1));
        $results = match ($activeType) {
            PublicSearch::MEMBERS => $search->members($locale, $query, $page),
            PublicSearch::TAGS => $search->tags($locale, $query, $page),
            default => $search->stories($locale, $query, $page),
        };
        $counts = $search->counts($locale, $query);
        $theme = $themes->default();
        $seoData = $seo->search($locale, $query, $activeType);

        return $views->make(
            theme: $theme,
            view: 'search.index',
            data: [
                'activeType' => $activeType,
                'counts' => $counts,
                'currentLocale' => $locale,
                'isSearchable' => $search->isSearchable($query),
                'query' => $query,
                'results' => $results,
                'searchTypes' => PublicSearch::TYPES,
                'seo' => $seoData,
                'theme' => $theme,
                'tabUrls' => $this->tabUrls($locale->code, $query),
            ],
            layoutData: [
                'currentLocale' => $locale,
                'localeUrls' => $localeUrls->search($activeLocales, $query, $activeType),
                'locales' => $activeLocales,
                'seo' => $seoData,
                'theme' => $theme,
            ],
        );
    }

    /** @return array<string, string> */
    private function tabUrls(string $locale, string $query): array
    {
        $urls = [];

        foreach (PublicSearch::TYPES as $type) {
            $urls[$type] = route('blog.search', [
                'locale' => $locale,
                'q' => $query,
                'type' => $type,
            ]);
        }

        return $urls;
    }
}
