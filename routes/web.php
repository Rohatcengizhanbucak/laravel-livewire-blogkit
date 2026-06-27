<?php

use App\Livewire\Public\CategoryShow;
use App\Livewire\Public\PostIndex;
use App\Livewire\Public\PostShow;
use App\Livewire\Public\TagShow;
use App\Support\Localization\LocaleResolver;
use App\Support\Seo\RobotsTxt;
use App\Support\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/', function (LocaleResolver $locales) {
    return redirect()->route('blog.index', ['locale' => $locales->default()->code], 301);
})->name('home');

Route::get('robots.txt', function (RobotsTxt $robots) {
    return response($robots->render(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

Route::get('sitemap.xml', function (SitemapBuilder $sitemap) {
    return response($sitemap->render(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

Route::prefix('{locale}')
    ->where(['locale' => '[A-Za-z]{2,12}'])
    ->group(function (): void {
        Route::get('blog', PostIndex::class)->name('blog.index');
        Route::get('blog/{slug}', PostShow::class)->name('blog.show');
        Route::get('categories/{slug}', CategoryShow::class)->name('blog.categories.show');
        Route::get('tags/{slug}', TagShow::class)->name('blog.tags.show');
    });

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
