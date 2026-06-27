<?php

namespace App\Livewire\Public;

use App\Models\Post;
use App\Models\PostTranslation;
use App\Support\Localization\LocaleResolver;
use App\Support\Localization\LocaleUrlFactory;
use App\Support\Seo\SeoManager;
use App\Support\Theming\ThemedPageViewFactory;
use App\Support\Theming\ThemeManager;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PostShow extends Component
{
    public string $locale;

    public string $slug;

    public function mount(string $locale, string $slug): void
    {
        $this->locale = $locale;
        $this->slug = $slug;
    }

    public function render(LocaleResolver $locales, SeoManager $seo, ThemeManager $themes, ThemedPageViewFactory $views, LocaleUrlFactory $localeUrls): View
    {
        $locale = $locales->resolve($this->locale);
        $activeLocales = $locales->active();
        $post = $this->post($locale->id, $this->slug);
        $translation = $post->translations->firstWhere('locale_id', $locale->id);

        abort_unless($translation instanceof PostTranslation, 404);

        $relatedPosts = Post::query()
            ->published()
            ->forLocale($locale)
            ->withPublicRelations($locale)
            ->whereKeyNot($post->id)
            ->when($post->category_id !== null, fn ($query) => $query->where('category_id', $post->category_id))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $theme = $themes->default();
        $seoData = $seo->post($post, $translation, $locale);

        return $views->make(
            theme: $theme,
            view: 'posts.show',
            data: [
                'currentLocale' => $locale,
                'post' => $post,
                'relatedPosts' => $relatedPosts,
                'seo' => $seoData,
                'theme' => $theme,
                'translation' => $translation,
            ],
            layoutData: [
                'currentLocale' => $locale,
                'localeUrls' => $localeUrls->post($post),
                'locales' => $activeLocales,
                'seo' => $seoData,
                'theme' => $theme,
            ],
        );
    }

    private function post(int $localeId, string $slug): Post
    {
        return Post::query()
            ->published()
            ->whereHas('translations', function ($query) use ($localeId, $slug): void {
                $query->where('locale_id', $localeId)->where('slug', $slug);
            })
            ->with([
                'translations.locale',
                'author',
                'category',
                'tags',
                'seoMetas',
            ])
            ->firstOrFail();
    }
}
