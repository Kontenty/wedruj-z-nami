# AGENTS.md — wedruj-z-nami

## Keep in mind

- Project: Laravel 13 + PHP 8.4, MariaDB, Svelte 5 + Inertia v3, Filament v4, Tailwind v4, Wayfinder, Pest v4, Pint.
- Locale/UI: Polish (`pl`) by default.
- Use named routes and Wayfinder for frontend ↔ backend links.
- Use `search-docs` before Laravel/Inertia/Filament changes.
- Activate the relevant skill for the domain you touch.

## Commands

- Full dev stack: `composer dev`
- All checks: `composer ci:check`
- PHP format: `vendor/bin/pint --dirty --format agent`
- Frontend format/lint/types: `npm run format`, `npm run lint`, `npm run types:check`
- Tests: `composer test` or `php artisan test --compact --filter=...`
- Build frontend: `npm run build`

## Core conventions

- Use existing directory structure; don’t add new base folders or change dependencies without approval.
- Don’t create docs unless explicitly requested.
- Prefer concise, maintainable changes.
- Run Pint after any PHP edit.
- Use factories in tests; always `actingAs()` for authenticated/Filament tests.
- Tests use MariaDB, not SQLite.
- Don’t delete tests without approval.

## Seed data & fixtures

- Seeding images live in `database/fixtures/images/` and are attached during seeding via `spatie/laravel-medialibrary`.
- Do not commit large fixture assets; they are ignored in `.gitignore`. Keep a lightweight reference set in the repo.

## Filament

- Use correct namespaces:
  - Forms: `Filament\Forms\Components\`
  - Layouts: `Filament\Schemas\Components\`
  - Tables: `Filament\Tables\Columns\`, `Filament\Tables\Filters\`
  - Schema utilities: `Filament\Schemas\Components\Utilities\`
  - Actions: `Filament\Actions\`
- `Repeater` uses `->schema()`.
- Use `Select::make('author_id')->relationship('author', 'name')` for BelongsTo fields.
- Use `->visibility('public')` for public files.
- Use `->columnSpanFull()` when a layout element should span all columns.
- Prefer `->live(onBlur: true)` on text inputs.

## Skills to activate when relevant

- `fortify-development` — auth
- `laravel-best-practices` — Laravel PHP
- `wayfinder-development` — frontend route wiring
- `pest-testing` — tests
- `inertia-svelte-development` — Svelte/Inertia UI
- `tailwindcss-development` — Tailwind UI
- `medialibrary-development` — media uploads/collections
