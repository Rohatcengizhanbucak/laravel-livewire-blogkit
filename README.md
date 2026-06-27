# Laravel Livewire BlogKit

An open-source Laravel and Livewire blog platform foundation for teams that want a clean, SEO-aware, multilingual, theme-ready publishing system.

> Status: early foundation. The repository already includes the Laravel 13 + Livewire 4 application shell, authentication starter kit, content-domain migrations, SEO metadata models, theme/palette models, CI, tests, and open-source project files. The public editor and admin publishing workflows are the next major milestones.

[![Tests](https://github.com/Rohatcengizhanbucak/laravel-livewire-blogkit/actions/workflows/tests.yml/badge.svg)](https://github.com/Rohatcengizhanbucak/laravel-livewire-blogkit/actions/workflows/tests.yml)
[![Lint](https://github.com/Rohatcengizhanbucak/laravel-livewire-blogkit/actions/workflows/lint.yml/badge.svg)](https://github.com/Rohatcengizhanbucak/laravel-livewire-blogkit/actions/workflows/lint.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Why This Exists

Most blog starters are either too small to grow or too opinionated to customize. BlogKit aims to sit in the useful middle:

- simple enough to understand from the first clone
- structured enough to become a serious open-source CMS/blog engine
- SEO-first instead of SEO-added-later
- multilingual and theme-ready at the data model level
- Laravel-native, Livewire-native, and easy to self-host

## Core Ideas

- **Content is translatable**: posts and pages store language-specific title, slug, excerpt, and body records.
- **SEO is first-class**: meta titles, descriptions, canonical URLs, robots flags, Open Graph fields, and schema payloads are modeled explicitly.
- **Themes are data-aware**: themes and color palettes are stored as records so public presentation can become dynamic.
- **Auth is already present**: the app starts from the official Laravel Livewire starter kit with Fortify, passkeys, two-factor authentication, and account settings.
- **The codebase should stay readable**: the first version favors clear Laravel conventions over clever abstractions.

## Tech Stack

- Laravel 13
- Livewire 4
- Flux UI
- Fortify authentication
- Tailwind CSS 4
- Vite 8
- SQLite by default for local development
- PHPUnit, Laravel Pint, and Larastan/PHPStan

## Requirements

- PHP 8.3 or newer
- Composer 2
- Node.js LTS with npm
- SQLite for the default local database

Windows users can use XAMPP PHP. The included `start.bat` looks for `C:\xampp\php\php.exe` first, then falls back to `php` from PATH.

## Quick Start

```bash
git clone https://github.com/Rohatcengizhanbucak/laravel-livewire-blogkit.git
cd laravel-livewire-blogkit

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

npm install
npm run build

php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

For Windows:

```bat
start.bat
```

For Windows with the Vite dev server:

```bat
start.bat dev
```

## Development

Run the Laravel app:

```bash
php artisan serve
```

Run Vite:

```bash
npm run dev
```

Run the full backend quality suite:

```bash
composer test
```

Run only application tests:

```bash
php artisan test
```

Run formatting:

```bash
composer lint
```

Run static analysis:

```bash
composer types:check
```

## Domain Model

The current foundation includes these content tables and models:

- `locales`: available languages and default locale state
- `themes`: selectable frontend theme definitions
- `theme_palettes`: per-theme color palettes
- `categories`: hierarchical post categories
- `tags`: post tags
- `posts`: publishable article records
- `post_translations`: localized post title, slug, excerpt, and content
- `pages`: publishable static pages
- `page_translations`: localized page title, slug, excerpt, and content
- `seo_metas`: polymorphic SEO records for posts, pages, and future content types

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for the longer design map.

## Planned Features

- Public blog index, category pages, tag pages, and post detail pages
- Admin publishing workflow for drafts, scheduled posts, and featured content
- Dynamic theme switching and user-selectable color palettes
- Multilingual route generation and `hreflang` output
- XML sitemap, RSS feed, canonical URLs, and structured data rendering
- Media library with image optimization
- Plugin-friendly extension points for themes and SEO integrations
- Import/export tools for open-source portability

The roadmap lives in [docs/ROADMAP.md](docs/ROADMAP.md).

## Repository Structure

```text
app/
  Models/                 Content, SEO, theme, locale, and auth models
database/
  migrations/             Schema for auth and blog platform records
  seeders/                Default locale, theme, category, tag, and sample post
resources/
  views/                  Blade and Livewire starter kit views
routes/
  web.php                 Public routes and authenticated dashboard
tests/
  Feature/                Application and architecture tests
docs/
  ARCHITECTURE.md         Technical design notes
  ROADMAP.md              Public roadmap
```

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

For security concerns, do not open a public issue. Read [SECURITY.md](SECURITY.md).

## License

BlogKit is open-source software licensed under the [MIT license](LICENSE).
