<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\SeoMeta;
use App\Models\Tag;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_content_can_be_translated_tagged_themed_and_described_for_seo(): void
    {
        $locale = Locale::query()->create([
            'code' => 'en',
            'name' => 'English',
            'is_default' => true,
        ]);

        $theme = Theme::query()->create([
            'key' => 'editorial',
            'name' => 'Editorial',
            'view_path' => 'themes.editorial',
            'is_default' => true,
        ]);

        $palette = $theme->palettes()->create([
            'key' => 'classic',
            'name' => 'Classic',
            'colors' => ['primary' => '#525252'],
            'is_default' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Engineering',
            'slug' => 'engineering',
        ]);

        $tag = Tag::query()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $post = Post::query()->create([
            'author_id' => User::factory()->create()->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        PostTranslation::query()->create([
            'post_id' => $post->id,
            'locale_id' => $locale->id,
            'title' => 'Modern Blog Architecture',
            'slug' => 'modern-blog-architecture',
            'content' => 'A multilingual and theme-aware content model.',
        ]);

        $post->tags()->attach($tag);

        SeoMeta::query()->create([
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'locale_id' => $locale->id,
            'meta_title' => 'Modern Blog Architecture',
            'schema_type' => 'Article',
            'schema' => ['@type' => 'Article'],
        ]);

        $this->assertTrue($palette->is_default);
        $this->assertSame('Engineering', $post->category->name);
        $this->assertSame('Modern Blog Architecture', $post->translations()->first()->title);
        $this->assertSame('Laravel', $post->tags()->first()->name);
        $this->assertSame('Article', $post->seoMetas()->first()->schema_type);
    }
}
