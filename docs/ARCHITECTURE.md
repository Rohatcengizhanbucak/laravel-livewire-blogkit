# Architecture

Laravel Livewire BlogKit is designed as a conventional Laravel application with a clear publishing domain. The first milestone is a strong foundation, not a hidden framework.

## Layers

### Content

Posts and pages are language-neutral records. Their localized text lives in translation tables:

- `posts` -> `post_translations`
- `pages` -> `page_translations`

This keeps publication state, author ownership, scheduling, and taxonomy independent from text language.

### Localization

`locales` stores available languages, default locale state, direction, and sort order. Future public routes should resolve locale early, then query translations through the active locale.

Expected URL direction:

```text
/en/blog/my-post
/tr/blog/yazim
```

### SEO

`seo_metas` is polymorphic. Posts, pages, and future content types can each own locale-aware SEO fields:

- meta title
- meta description
- canonical URL
- Open Graph title/description/image
- robots index/follow
- schema type
- schema JSON payload

Rendering should happen through a dedicated SEO view component or service so templates do not duplicate tag logic.

### Taxonomy

Categories are hierarchical and tags are flat. Posts can belong to one category and many tags.

### Themes

Themes and palettes are stored as data:

- `themes.key` identifies the active visual package.
- `themes.view_path` points to the Blade theme namespace/path.
- `theme_palettes.colors` stores named color tokens.

The intended rendering flow is:

```text
request -> active locale -> active theme -> active palette -> public view
```

### Authentication

The project starts with the Laravel Livewire starter kit. Fortify, passkeys, two-factor authentication, and account settings are present so admin features can be added without rebuilding authentication.

## Near-Term Modules

- `App\Livewire\Public\PostIndex`
- `App\Livewire\Public\PostShow`
- `App\Livewire\Public\Search`
- `App\Livewire\Admin\Posts`
- `App\Support\Seo`
- `App\Support\Theming`
- `App\Support\Localization`

## Design Rules

- Keep database ownership clear.
- Keep SEO rendering centralized.
- Keep themes replaceable.
- Keep locale resolution explicit.
- Prefer Laravel conventions unless a real abstraction removes complexity.
