# Contributing

Thanks for considering a contribution to Laravel Livewire BlogKit.

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan test
```

## Development Standards

- Keep changes small and easy to review.
- Prefer Laravel conventions.
- Add tests for schema, model, route, or behavior changes.
- Update documentation when behavior or setup changes.
- Do not commit secrets, local databases, `vendor`, `node_modules`, or build output.

## Pull Requests

Before opening a pull request:

```bash
composer test
npm run build
```

Include:

- what changed
- why it changed
- screenshots for UI changes
- migration notes when schema changes
- any follow-up work

## Commit Style

Use short, direct commit messages:

```text
Add multilingual post schema
Document theme architecture
Fix SEO meta casting
```
