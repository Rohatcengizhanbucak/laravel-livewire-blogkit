<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\SeoMeta;
use App\Models\Tag;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $locale = Locale::query()->firstOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'native_name' => 'English',
                'is_default' => true,
                'is_active' => true,
            ],
        );

        Locale::query()->firstOrCreate(
            ['code' => 'tr'],
            [
                'name' => 'Turkish',
                'native_name' => 'Turkce',
                'is_active' => true,
                'sort_order' => 10,
            ],
        );

        $theme = Theme::query()->firstOrCreate(
            ['key' => 'default'],
            [
                'name' => 'Default Editorial',
                'description' => 'Clean editorial layout for content-heavy blogs.',
                'view_path' => 'themes.default',
                'is_default' => true,
            ],
        );

        $theme->palettes()->firstOrCreate(
            ['key' => 'midnight'],
            [
                'name' => 'Midnight',
                'colors' => [
                    'background' => '#111827',
                    'surface' => '#ffffff',
                    'primary' => '#2563eb',
                    'accent' => '#16a34a',
                    'text' => '#111827',
                ],
                'is_default' => true,
            ],
        );

        $author = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $category = Category::query()->firstOrCreate(
            ['slug' => 'engineering'],
            [
                'name' => 'Engineering',
                'description' => 'Technical notes, architecture decisions, and build logs.',
                'color' => '#2563eb',
            ],
        );

        $tag = Tag::query()->firstOrCreate(
            ['slug' => 'laravel'],
            ['name' => 'Laravel'],
        );

        $post = Post::query()->firstOrCreate(
            ['author_id' => $author->id, 'category_id' => $category->id, 'status' => 'published'],
            [
                'reading_time' => 4,
                'is_featured' => true,
                'published_at' => now(),
            ],
        );

        $post->tags()->syncWithoutDetaching([$tag->id]);

        PostTranslation::query()->firstOrCreate(
            ['post_id' => $post->id, 'locale_id' => $locale->id],
            [
                'title' => 'Designing a Modern Laravel Blog Foundation',
                'slug' => Str::slug('Designing a Modern Laravel Blog Foundation'),
                'excerpt' => 'A first look at the content, SEO, theme, and locale architecture.',
                'content' => 'This starter post documents the direction of the open-source blog platform.',
            ],
        );

        SeoMeta::query()->firstOrCreate(
            ['seoable_type' => Post::class, 'seoable_id' => $post->id, 'locale_id' => $locale->id],
            [
                'meta_title' => 'Modern Laravel Blog Foundation',
                'meta_description' => 'Open-source Laravel and Livewire blog platform foundation with multilingual content, themes, and SEO metadata.',
                'schema_type' => 'Article',
                'schema' => [
                    '@type' => 'Article',
                    'headline' => 'Designing a Modern Laravel Blog Foundation',
                ],
            ],
        );
    }
}
