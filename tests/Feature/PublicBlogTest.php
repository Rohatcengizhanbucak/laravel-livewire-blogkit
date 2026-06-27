<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Tag;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBlogTest extends TestCase
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
            'colors' => [
                'background' => '#f8fafc',
                'surface' => '#ffffff',
                'primary' => '#525252',
                'accent' => '#16a34a',
                'text' => '#0f172a',
            ],
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

    public function test_blog_index_exposes_only_public_posts_for_the_current_locale(): void
    {
        $this->createPost('Visible Published Post', 'visible-published-post');
        $this->createPost('Draft Secrets', 'draft-secrets', postAttributes: ['status' => 'draft']);
        $this->createPost('Future Scheduled', 'future-scheduled', postAttributes: ['published_at' => now()->addDay()]);
        $this->createPost('Turkish Only', 'yalnizca-turkce', locale: $this->tr);

        $deleted = $this->createPost('Deleted Public Post', 'deleted-public-post');
        $deleted->delete();

        $response = $this->get(route('blog.index', ['locale' => 'en']));

        $response
            ->assertOk()
            ->assertSee('Visible Published Post')
            ->assertDontSee('Draft Secrets')
            ->assertDontSee('Future Scheduled')
            ->assertDontSee('Turkish Only')
            ->assertDontSee('Deleted Public Post');
    }

    public function test_public_blog_uses_a_stable_system_font_surface(): void
    {
        $this->createPost('Stable Font Post', 'stable-font-post');

        $this->get(route('blog.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('blog-public-surface', false)
            ->assertDontSee('@font-face', false)
            ->assertDontSee('rel="preload" as="font"', false);
    }

    public function test_public_navbar_search_form_targets_the_current_locale(): void
    {
        $this->get(route('blog.index', ['locale' => 'tr']))
            ->assertOk()
            ->assertSee('role="search"', false)
            ->assertSee('action="'.route('blog.search', ['locale' => 'tr']).'"', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="type"', false)
            ->assertSee('value="stories"', false)
            ->assertSee(trans('blog.nav.search_placeholder', [], 'tr'));
    }

    public function test_blog_index_translates_public_ui_for_the_current_locale(): void
    {
        $this->createPost('Turkce Yayin', 'turkce-yayin', locale: $this->tr);

        $this->get(route('blog.index', ['locale' => 'tr']))
            ->assertOk()
            ->assertSee('Açık Blog')
            ->assertSee('SEO odaklı Laravel yayıncılığı')
            ->assertSee('BlogKit için editör notları, teknik yazılar ve ürün güncellemeleri.')
            ->assertSee('Turkce Yayin')
            ->assertSee('3 dk okuma')
            ->assertDontSee('SEO-aware Laravel publishing')
            ->assertDontSee('Editorial notes, technical articles')
            ->assertDontSee('No published posts are available');
    }

    public function test_post_language_switcher_points_to_the_translated_post_slug(): void
    {
        $post = $this->createPost('English Detail Post', 'english-detail-post');

        PostTranslation::query()->create([
            'post_id' => $post->id,
            'locale_id' => $this->tr->id,
            'title' => 'Turkish Detail Post',
            'slug' => 'turkish-detail-post',
            'excerpt' => 'Turkish detail excerpt.',
            'content' => 'Turkish detail content.',
        ]);

        $this->get(route('blog.show', ['locale' => 'tr', 'slug' => 'turkish-detail-post']))
            ->assertOk()
            ->assertSee('href="'.route('blog.show', ['locale' => 'en', 'slug' => 'english-detail-post']).'"', false)
            ->assertDontSee('href="'.route('blog.index', ['locale' => 'en']).'"', false);
    }

    public function test_post_language_switcher_disables_missing_translations(): void
    {
        $this->createPost('Turkish Only Detail', 'turkish-only-detail', locale: $this->tr);

        $this->get(route('blog.show', ['locale' => 'tr', 'slug' => 'turkish-only-detail']))
            ->assertOk()
            ->assertSee('aria-disabled="true"', false)
            ->assertDontSee('href="'.route('blog.index', ['locale' => 'en']).'"', false);
    }

    public function test_search_lists_only_public_posts_for_the_current_locale(): void
    {
        $this->createPost('Needle Public Article', 'needle-public-article');
        $this->createPost('Needle Draft Secret', 'needle-draft-secret', postAttributes: ['status' => 'draft']);
        $this->createPost('Needle Future Article', 'needle-future-article', postAttributes: ['published_at' => now()->addDay()]);
        $this->createPost('Needle Turkish Article', 'needle-turkish-article', locale: $this->tr);
        $this->createPost('Other Public Article', 'other-public-article');

        $deleted = $this->createPost('Needle Deleted Article', 'needle-deleted-article');
        $deleted->delete();

        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'stories']))
            ->assertOk()
            ->assertSee('Results for')
            ->assertSee('Stories')
            ->assertSee('Members')
            ->assertSee('Tags')
            ->assertSee('Needle Public Article')
            ->assertDontSee('Needle Draft Secret')
            ->assertDontSee('Needle Future Article')
            ->assertDontSee('Needle Turkish Article')
            ->assertDontSee('Needle Deleted Article')
            ->assertDontSee('Other Public Article');
    }

    public function test_search_members_lists_only_authors_with_public_current_locale_posts(): void
    {
        $publicAuthor = User::factory()->create([
            'name' => 'Needle Writer',
            'email' => 'needle-public@example.com',
        ]);
        $draftAuthor = User::factory()->create([
            'name' => 'Needle Draft Writer',
            'email' => 'needle-draft@example.com',
        ]);
        $privateAuthor = User::factory()->create([
            'name' => 'Needle Private Member',
            'email' => 'needle-private@example.com',
        ]);
        $turkishAuthor = User::factory()->create([
            'name' => 'Needle Turkish Writer',
            'email' => 'needle-tr@example.com',
        ]);

        $this->createPost('Needle Public By Author', 'needle-public-by-author', postAttributes: ['author_id' => $publicAuthor->id]);
        $this->createPost('Needle Draft By Author', 'needle-draft-by-author', postAttributes: [
            'author_id' => $draftAuthor->id,
            'status' => 'draft',
        ]);
        $this->createPost('Needle Turkish By Author', 'needle-turkish-by-author', locale: $this->tr, postAttributes: ['author_id' => $turkishAuthor->id]);

        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'members']))
            ->assertOk()
            ->assertSee('Members')
            ->assertSee('Needle Writer')
            ->assertSee('Needle Public By Author')
            ->assertDontSee('Needle Draft Writer')
            ->assertDontSee('Needle Private Member')
            ->assertDontSee('Needle Turkish Writer')
            ->assertDontSee($publicAuthor->email)
            ->assertDontSee($draftAuthor->email)
            ->assertDontSee($privateAuthor->email)
            ->assertDontSee($turkishAuthor->email);
    }

    public function test_search_tags_lists_only_tags_attached_to_public_current_locale_posts(): void
    {
        $publicTag = Tag::query()->create([
            'name' => 'Needle Tools',
            'slug' => 'needle-tools',
            'description' => 'Neutral public tag description.',
        ]);
        $draftTag = Tag::query()->create([
            'name' => 'Needle Draft',
            'slug' => 'needle-draft',
            'description' => 'Draft-only tag description.',
        ]);
        $turkishTag = Tag::query()->create([
            'name' => 'Needle Turkish',
            'slug' => 'needle-turkish',
            'description' => 'Turkish-only tag description.',
        ]);
        $orphanTag = Tag::query()->create([
            'name' => 'Needle Orphan',
            'slug' => 'needle-orphan',
            'description' => 'No public post is attached.',
        ]);

        $this->createPost('Needle Tagged Article', 'needle-tagged-article', tags: [$publicTag]);
        $this->createPost('Needle Draft Tagged Article', 'needle-draft-tagged-article', postAttributes: ['status' => 'draft'], tags: [$draftTag]);
        $this->createPost('Needle Turkish Tagged Article', 'needle-turkish-tagged-article', locale: $this->tr, tags: [$turkishTag]);

        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'tags']))
            ->assertOk()
            ->assertSee('Tags')
            ->assertSee('Needle Tools')
            ->assertSee('Neutral public tag description.')
            ->assertDontSee('Needle Draft')
            ->assertDontSee('Needle Turkish')
            ->assertDontSee('Needle Orphan')
            ->assertDontSee($draftTag->description)
            ->assertDontSee($turkishTag->description)
            ->assertDontSee($orphanTag->description);
    }

    public function test_search_tabs_preserve_the_query_value(): void
    {
        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'stories']))
            ->assertOk()
            ->assertSee('href="'.e(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'members'])).'"', false)
            ->assertSee('href="'.e(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'tags'])).'"', false);
    }

    public function test_invalid_search_type_falls_back_to_stories(): void
    {
        $this->createPost('Fallback Needle Story', 'fallback-needle-story');

        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'unknown']))
            ->assertOk()
            ->assertSee('Fallback Needle Story')
            ->assertSee('href="'.e(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'stories'])).'"', false);
    }

    public function test_public_search_surface_uses_neutral_accents(): void
    {
        $this->createPost('Neutral Public Article', 'neutral-public-article');

        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'neutral', 'type' => 'stories']))
            ->assertOk()
            ->assertDontSee('text-blue', false)
            ->assertDontSee('border-blue', false)
            ->assertDontSee('bg-blue', false)
            ->assertDontSee('text-slate', false)
            ->assertDontSee('border-slate', false)
            ->assertDontSee('bg-slate', false)
            ->assertDontSee('#2563eb', false)
            ->assertDontSee('#475569', false);
    }

    public function test_search_short_query_does_not_list_results(): void
    {
        $this->createPost('A Matching Public Article', 'a-matching-public-article');

        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'a', 'type' => 'stories']))
            ->assertOk()
            ->assertSee(trans('blog.search.short_query', [], 'en'))
            ->assertDontSee('A Matching Public Article');
    }

    public function test_search_language_switcher_preserves_the_query_and_type(): void
    {
        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'members']))
            ->assertOk()
            ->assertSee('href="'.e(route('blog.search', ['locale' => 'tr', 'q' => 'needle', 'type' => 'members'])).'"', false);
    }

    public function test_search_renders_noindex_canonical_seo_head_without_json_ld(): void
    {
        $this->createPost('Needle Public Article', 'needle-public-article');

        $this->get(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'tags']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow" />', false)
            ->assertSee('<link rel="canonical" href="'.e(route('blog.search', ['locale' => 'en', 'q' => 'needle', 'type' => 'tags'])).'" />', false)
            ->assertDontSee('<script type="application/ld+json">', false);
    }

    public function test_locale_slug_resolution_requires_an_active_locale_and_matching_translation(): void
    {
        $this->createPost('Localized Post', 'localized-post');

        Locale::query()->create([
            'code' => 'de',
            'name' => 'German',
            'is_active' => false,
        ]);

        $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'localized-post']))
            ->assertOk()
            ->assertSee('Localized Post');

        $this->get(route('blog.show', ['locale' => 'tr', 'slug' => 'localized-post']))->assertNotFound();
        $this->get('/de/blog')->assertNotFound();
        $this->get('/zz/blog/localized-post')->assertNotFound();
    }

    public function test_category_and_tag_pages_only_list_public_posts(): void
    {
        $this->createPost('Visible Hub Post', 'visible-hub-post');
        $this->createPost('Draft Hub Post', 'draft-hub-post', postAttributes: ['status' => 'draft']);

        $this->get(route('blog.categories.show', ['locale' => 'en', 'slug' => 'engineering']))
            ->assertOk()
            ->assertSee('Visible Hub Post')
            ->assertDontSee('Draft Hub Post');

        $this->get(route('blog.tags.show', ['locale' => 'en', 'slug' => 'laravel']))
            ->assertOk()
            ->assertSee('Visible Hub Post')
            ->assertDontSee('Draft Hub Post');
    }

    public function test_root_redirects_to_the_default_locale_blog(): void
    {
        $this->get('/')
            ->assertRedirectToRoute('blog.index', ['locale' => 'en']);
    }

    /** @param  array<string, mixed>  $postAttributes */
    private function createPost(
        string $title,
        string $slug,
        ?Locale $locale = null,
        array $postAttributes = [],
        ?array $tags = null,
    ): Post {
        $locale ??= $this->en;

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
            'locale_id' => $locale->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $title.' excerpt for search snippets.',
            'content' => $title.' content for public readers.',
        ]);

        $post->tags()->attach(array_map(
            fn (Tag $tag): int => $tag->id,
            $tags ?? [$this->tag],
        ));

        return $post;
    }
}
