<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\SeoMeta;
use App\Models\Tag;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoSurfaceTest extends TestCase
{
    use RefreshDatabase;

    private Locale $en;

    private Locale $tr;

    private Category $category;

    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.name' => 'BlogKit', 'app.url' => 'http://localhost']);

        $this->en = Locale::query()->create([
            'code' => 'en',
            'name' => 'English',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->tr = Locale::query()->create([
            'code' => 'tr',
            'name' => 'Turkish',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $theme = Theme::query()->create([
            'key' => 'default',
            'name' => 'Default Editorial',
            'view_path' => 'themes.default',
            'is_default' => true,
            'is_active' => true,
        ]);

        $theme->palettes()->create([
            'key' => 'classic',
            'name' => 'Classic',
            'colors' => ['primary' => '#2563eb', 'accent' => '#16a34a', 'text' => '#0f172a'],
            'is_default' => true,
        ]);

        $this->category = Category::query()->create([
            'name' => 'Engineering',
            'slug' => 'engineering',
            'description' => 'Technical publishing notes.',
            'is_active' => true,
        ]);

        $this->tag = Tag::query()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
            'description' => 'Laravel articles and implementation notes.',
        ]);
    }

    public function test_post_detail_renders_google_ready_seo_head(): void
    {
        $post = $this->createPost('Search Friendly Post', 'search-friendly-post');

        PostTranslation::query()->create([
            'post_id' => $post->id,
            'locale_id' => $this->tr->id,
            'title' => 'Arama Dostu Yazi',
            'slug' => 'arama-dostu-yazi',
            'excerpt' => 'Turkish alternate excerpt.',
            'content' => 'Turkish alternate content.',
        ]);

        SeoMeta::query()->create([
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'locale_id' => $this->en->id,
            'meta_title' => 'Custom Google Title',
            'meta_description' => 'A precise meta description for Google search result snippets.',
            'og_title' => 'Open Graph Google Title',
            'og_description' => 'Open Graph description for share previews.',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $canonical = route('blog.show', ['locale' => 'en', 'slug' => 'search-friendly-post']);
        $trAlternate = route('blog.show', ['locale' => 'tr', 'slug' => 'arama-dostu-yazi']);

        $response = $this->get($canonical);

        $response
            ->assertOk()
            ->assertSee('<title>Custom Google Title</title>', false)
            ->assertSee('<meta name="description" content="A precise meta description for Google search result snippets." />', false)
            ->assertSee('<link rel="canonical" href="'.$canonical.'" />', false)
            ->assertSee('<meta name="robots" content="index,follow" />', false)
            ->assertSee('<meta property="og:title" content="Open Graph Google Title" />', false)
            ->assertSee('<script type="application/ld+json">', false)
            ->assertSee('BlogPosting')
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="tr"', false)
            ->assertSee('href="'.$trAlternate.'"', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_sitemap_contains_only_indexable_public_urls(): void
    {
        $this->createPost('Indexed Result', 'indexed-result');
        $this->createPost('Draft Result', 'draft-result', postAttributes: ['status' => 'draft']);
        $this->createPost('Future Result', 'future-result', postAttributes: ['published_at' => now()->addDay()]);
        $this->createPost('Noindex Result', 'noindex-result', postAttributes: ['allow_index' => false]);

        $metaNoindex = $this->createPost('Meta Noindex Result', 'meta-noindex-result');

        SeoMeta::query()->create([
            'seoable_type' => Post::class,
            'seoable_id' => $metaNoindex->id,
            'locale_id' => $this->en->id,
            'robots_index' => false,
            'robots_follow' => true,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertStringStartsWith('application/xml', (string) $response->headers->get('content-type'));

        $response
            ->assertSee(route('blog.index', ['locale' => 'en']), false)
            ->assertSee(route('blog.show', ['locale' => 'en', 'slug' => 'indexed-result']), false)
            ->assertDontSee('draft-result')
            ->assertDontSee('future-result')
            ->assertDontSee('noindex-result')
            ->assertDontSee('meta-noindex-result');
    }

    public function test_robots_txt_is_dynamic_plain_text_and_points_to_the_sitemap(): void
    {
        $response = $this->get(route('robots'));

        $response
            ->assertOk()
            ->assertSee('User-agent: *')
            ->assertSee('Allow: /')
            ->assertSee('Sitemap: '.route('sitemap'));

        $this->assertStringStartsWith('text/plain', (string) $response->headers->get('content-type'));
    }

    /** @param  array<string, mixed>  $postAttributes */
    private function createPost(string $title, string $slug, array $postAttributes = []): Post
    {
        $post = Post::query()->create([
            'category_id' => $this->category->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'reading_time' => 3,
            'allow_index' => true,
            ...$postAttributes,
        ]);

        PostTranslation::query()->create([
            'post_id' => $post->id,
            'locale_id' => $this->en->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $title.' excerpt for search snippets.',
            'content' => $title.' content for public readers.',
        ]);

        $post->tags()->attach($this->tag);

        return $post;
    }
}
