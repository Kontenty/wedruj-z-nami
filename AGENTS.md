# AGENTS.md — wedruj-z-nami

## Stack

- Laravel 13, PHP 8.5, MariaDB (via Docker)
- Svelte 5 + Inertia v3 (frontend pages in `resources/js/pages/`)
- Filament v4 admin panel at `/cms` (resources in `app/Filament/Resources/`)
- Tailwind v4, shadcn-svelte (components in `resources/js/components/ui/`)
- Wayfinder for typed route functions (`resources/js/actions/`, `resources/js/routes/`)
- Pest v4 for tests, Pint for PHP formatting

## Commands

```bash
# Full dev server (artisan + queue + pail + vite concurrently)
composer dev

# Run all checks (lint → format → types → test)
composer ci:check

# PHP formatting (always after editing PHP files)
vendor/bin/pint --dirty --format agent

# Frontend formatting
npm run format

# Frontend lint
npm run lint

# Frontend type check
npm run types:check

# Tests (clears config, prepares test DB, checks pint, runs pest)
composer test

# Run specific test
php artisan test --compact --filter=testName

# Create test
php artisan make:test --pest SomeFeatureTest

# Build frontend assets
npm run build
```

## Architecture

- **Frontend entry**: `resources/js/app.ts` → Inertia renders Svelte pages from `resources/js/pages/`
- **Routes**: `routes/web.php` (public + auth), `routes/settings.php` (user settings)
- **Controllers**: `app/Http/Controllers/` — use Wayfinder-generated types, not hardcoded URLs
- **Models**: `app/Models/` — domain: SightseeingObject, ObjectType, Voivodeship, Article, User
- **Admin**: Filament panel at `/cms`, resources in `app/Filament/Resources/` (Articles, ObjectTypes, SightseeingObjects)
- **Actions**: `app/Actions/` for Fortify auth actions
- **Factories**: `database/factories/` — all models have factories
- **Migrations**: MariaDB-specific, test DB is `wedruj_z_nami_testing`

## Testing

- Tests use MariaDB, not SQLite. The `test:prepare-database` artisan command creates the test DB.
- PHPUnit env is set in `phpunit.xml` — DB connection is `mariadb` on `127.0.0.1:3306`.
- Always `actingAs()` before testing authenticated/Filament functionality.
- Use factories for model creation in tests.
- Do not delete tests without approval.

## Conventions

- Polish locale (`pl`) is the default. Route names and UI text are Polish.
- Use `composer dev` to run the full stack, not separate commands.
- ESLint ignores generated files: `resources/js/actions/**`, `resources/js/components/ui/*`, `resources/js/routes/**`, `resources/js/wayfinder/**`. Do not lint or edit these directly.
- Prettier also ignores `resources/js/components/ui/*`.
- Tailwind classes go through `cn()` / `cva()` / `clsx()` utilities (configured in prettier).
- Import order enforced: builtin → external → internal → parent → sibling → index, alphabetized.
- TypeScript `@/` alias maps to `resources/js/`.
- Use `search-docs` tool for Laravel/Inertia/Filament docs before making changes.
- Run `vendor/bin/pint --dirty --format agent` after any PHP edits.

## Skills

Activate relevant skills when working in their domain (see `boost.json` for the list):

- `fortify-development` — auth changes
- `laravel-best-practices` — PHP backend work
- `wayfinder-development` — frontend ↔ backend route wiring
- `pest-testing` — writing or fixing tests
- `inertia-svelte-development` — Svelte page/component work
- `tailwindcss-development` — styling
- `medialibrary-development` — file uploads, media collections
