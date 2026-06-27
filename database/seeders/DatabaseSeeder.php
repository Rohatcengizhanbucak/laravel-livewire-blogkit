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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $enLocale = Locale::query()->updateOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'native_name' => 'English',
                'is_default' => true,
                'is_active' => true,
            ],
        );

        $trLocale = Locale::query()->updateOrCreate(
            ['code' => 'tr'],
            [
                'name' => 'Turkish',
                'native_name' => 'Türkçe',
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

        $theme->palettes()->updateOrCreate(
            ['key' => 'midnight'],
            [
                'name' => 'Midnight',
                'colors' => [
                    'background' => '#111827',
                    'surface' => '#ffffff',
                    'primary' => '#525252',
                    'accent' => '#16a34a',
                    'text' => '#111827',
                ],
                'is_default' => true,
            ],
        );

        $author = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ],
        );

        $category = Category::query()->updateOrCreate(
            ['slug' => 'engineering'],
            [
                'name' => 'Engineering',
                'description' => 'Technical notes, architecture decisions, and build logs.',
                'color' => '#525252',
            ],
        );

        $tag = Tag::query()->firstOrCreate(
            ['slug' => 'laravel'],
            ['name' => 'Laravel'],
        );

        $post = Post::query()->updateOrCreate(
            ['category_id' => $category->id, 'is_featured' => true],
            [
                'author_id' => $author->id,
                'status' => 'published',
                'reading_time' => 4,
                'is_featured' => true,
                'allow_index' => true,
                'published_at' => now(),
            ],
        );

        $post->tags()->syncWithoutDetaching([$tag->id]);

        PostTranslation::query()->firstOrCreate(
            ['post_id' => $post->id, 'locale_id' => $enLocale->id],
            [
                'title' => 'Designing a Modern Laravel Blog Foundation',
                'slug' => Str::slug('Designing a Modern Laravel Blog Foundation'),
                'excerpt' => 'A first look at the content, SEO, theme, and locale architecture.',
                'content' => 'This starter post documents the direction of the open-source blog platform.',
            ],
        );

        PostTranslation::query()->firstOrCreate(
            ['post_id' => $post->id, 'locale_id' => $trLocale->id],
            [
                'title' => 'Modern Laravel Blog Temeli Tasarlamak',
                'slug' => Str::slug('Modern Laravel Blog Temeli Tasarlamak'),
                'excerpt' => 'İçerik, SEO, tema ve dil mimarisine ilk bakış.',
                'content' => 'Bu başlangıç yazısı, açık kaynak blog platformunun yönünü anlatır.',
            ],
        );

        SeoMeta::query()->firstOrCreate(
            ['seoable_type' => Post::class, 'seoable_id' => $post->id, 'locale_id' => $enLocale->id],
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

        SeoMeta::query()->firstOrCreate(
            ['seoable_type' => Post::class, 'seoable_id' => $post->id, 'locale_id' => $trLocale->id],
            [
                'meta_title' => 'Modern Laravel Blog Temeli',
                'meta_description' => 'Çok dilli içerik, tema ve SEO metadatası içeren açık kaynak Laravel ve Livewire blog platformu temeli.',
                'schema_type' => 'Article',
                'schema' => [
                    '@type' => 'Article',
                    'headline' => 'Modern Laravel Blog Temeli Tasarlamak',
                ],
            ],
        );
    }
}
