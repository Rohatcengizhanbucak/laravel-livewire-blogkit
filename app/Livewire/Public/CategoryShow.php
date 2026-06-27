<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Support\Localization\LocaleResolver;
use App\Support\Seo\SeoManager;
use App\Support\Theming\ThemedPageViewFactory;
use App\Support\Theming\ThemeManager;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class CategoryShow extends Component
{
    public string $locale;

    public string $slug;

    public function mount(string $locale, string $slug): void
    {
        $this->locale = $locale;
        $this->slug = $slug;
    }

    public function render(LocaleResolver $locales, SeoManager $seo, ThemeManager $themes, ThemedPageViewFactory $views): View
    {
        $locale = $locales->resolve($this->locale);
        $category = Category::query()->active()->where('slug', $this->slug)->firstOrFail();
        $page = max(1, (int) request()->query('page', 1));
        $posts = $this->posts($category, $locale, $page);

        abort_if($page > 1 && $posts->count() === 0, 404);

        $theme = $themes->default();
        $seoData = $seo->taxonomy($category, $locale, 'category', $page, $posts->total());

        return $views->make(
            theme: $theme,
            view: 'taxonomy.show',
            data: [
                'currentLocale' => $locale,
                'posts' => $posts,
                'seo' => $seoData,
                'taxonomy' => $category,
                'taxonomyType' => 'category',
                'theme' => $theme,
            ],
            layoutData: [
                'currentLocale' => $locale,
                'locales' => $locales->active(),
                'seo' => $seoData,
                'theme' => $theme,
            ],
        );
    }

    /** @return LengthAwarePaginator<int, Post> */
    private function posts(Category $category, Locale $locale, int $page): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->forLocale($locale)
            ->withPublicRelations($locale)
            ->where('category_id', $category->id)
            ->orderByDesc('published_at')
            ->paginate(perPage: 10, page: $page);
    }
}
