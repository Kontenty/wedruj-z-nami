# AGENTS.md — wedruj-z-nami

## Stack

- Laravel 13, PHP 8.4, MariaDB (via Docker)
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

## PHP Conventions

- Use curly braces for control structures, even single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`.
- Explicit return type declarations and type hints for all method parameters.
- Use PHPDoc blocks over inline comments. Only inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

## Filament v4 Conventions

- **Namespaces**: `Filament\Forms\Components\`, `Filament\Schemas\Components\`, `Filament\Tables\Columns\`, `Filament\Tables\Filters\`, `Filament\Schemas\Components\Utilities\` (Get/Set), `Filament\Actions\` (never sub-namespaces).
- `Repeater` uses `->schema()`, not `->fields()`.
- Use `Select::make('author_id')->relationship('author', 'name')` — `BelongsToSelect` doesn't exist in v4.
- File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- `Grid`, `Section`, `Fieldset`, `Repeater` don't span all columns by default — use `->columnSpanFull()`.
- Never add `->dehydrated(false)` to fields that need to be saved.
- Property types on `Page`/`Resource`/`Widget`: `$navigationIcon` is `string | BackedEnum | null`, `$view` is `protected string` (not static).
- Use `$get` / `$set` from `Filament\Schemas\Components\Utilities\` for reactive form logic.
- Prefer `->live(onBlur: true)` on text inputs.

### Filament Testing

- Always `actingAs()` before testing panel functionality.
- Edit pages: pass `['record' => $id]`, use `->call('save')` (not `->call('create')`).
- Validation failures: `->assertNotNotified()` + `->assertHasFormErrors()`.

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

## Don'ts

- Don't create verification scripts or tinker when tests cover that functionality.
- Don't change dependencies without approval.
- Don't create new base folders without approval.
- Don't create documentation files unless explicitly requested.

## Skills

Activate relevant skills when working in their domain (see `boost.json` for the list):

- `fortify-development` — auth changes
- `laravel-best-practices` — PHP backend work
- `wayfinder-development` — frontend ↔ backend route wiring
- `pest-testing` — writing or fixing tests
- `inertia-svelte-development` — Svelte page/component work
- `tailwindcss-development` — styling
- `medialibrary-development` — file uploads, media collections

<!-- gitnexus:start -->
# GitNexus — Code Intelligence

This project is indexed by GitNexus as **wedruj-z-nami** (1067 symbols, 1830 relationships, 23 execution flows). Use the GitNexus MCP tools to understand code, assess impact, and navigate safely.

> If any GitNexus tool warns the index is stale, run `npx gitnexus analyze` in terminal first.

## Always Do

- **MUST run impact analysis before editing any symbol.** Before modifying a function, class, or method, run `gitnexus_impact({target: "symbolName", direction: "upstream"})` and report the blast radius (direct callers, affected processes, risk level) to the user.
- **MUST run `gitnexus_detect_changes()` before committing** to verify your changes only affect expected symbols and execution flows.
- **MUST warn the user** if impact analysis returns HIGH or CRITICAL risk before proceeding with edits.
- When exploring unfamiliar code, use `gitnexus_query({query: "concept"})` to find execution flows instead of grepping. It returns process-grouped results ranked by relevance.
- When you need full context on a specific symbol — callers, callees, which execution flows it participates in — use `gitnexus_context({name: "symbolName"})`.

## Never Do

- NEVER edit a function, class, or method without first running `gitnexus_impact` on it.
- NEVER ignore HIGH or CRITICAL risk warnings from impact analysis.
- NEVER rename symbols with find-and-replace — use `gitnexus_rename` which understands the call graph.
- NEVER commit changes without running `gitnexus_detect_changes()` to check affected scope.

## Resources

| Resource | Use for |
|----------|---------|
| `gitnexus://repo/wedruj-z-nami/context` | Codebase overview, check index freshness |
| `gitnexus://repo/wedruj-z-nami/clusters` | All functional areas |
| `gitnexus://repo/wedruj-z-nami/processes` | All execution flows |
| `gitnexus://repo/wedruj-z-nami/process/{name}` | Step-by-step execution trace |

## CLI

| Task | Read this skill file |
|------|---------------------|
| Understand architecture / "How does X work?" | `.claude/skills/gitnexus/gitnexus-exploring/SKILL.md` |
| Blast radius / "What breaks if I change X?" | `.claude/skills/gitnexus/gitnexus-impact-analysis/SKILL.md` |
| Trace bugs / "Why is X failing?" | `.claude/skills/gitnexus/gitnexus-debugging/SKILL.md` |
| Rename / extract / split / refactor | `.claude/skills/gitnexus/gitnexus-refactoring/SKILL.md` |
| Tools, resources, schema reference | `.claude/skills/gitnexus/gitnexus-guide/SKILL.md` |
| Index, status, clean, wiki CLI commands | `.claude/skills/gitnexus/gitnexus-cli/SKILL.md` |

<!-- gitnexus:end -->
