# Implementation Prompt: RFC-001

**Title:** Database Foundation & Core Models  
**ID:** RFC-001  
**Brief Description:** Establish MariaDB 10.11 database with all domain models, migrations, relationships, scopes, seeders, and tests.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "category" = *kategoria*; "article" = *artykuł*

---

You are implementing RFC-001 for the Kanon project — a Polish sightseeing objects catalog. This is the **foundation RFC** that all subsequent RFCs depend on.

## What to Build

1. **Switch database from SQLite to MariaDB**
2. **Create migrations** for: `wojewodztwa`, `kategorie`, `obiekty`, `object_category`, `artykuly`
3. **Create Eloquent models** with relationships, scopes, and slug generation
4. **Seed the database** with 16 Polish voivodeships, sample categories, sample objects (including UNESCO and polygon geometry), and sample articles
5. **Defer search integration** to a later RFC; do not add Scout or another search package in this milestone
6. **Write Pest tests** for all models, relationships, and scopes

## Key Files to Create/Modify

- `.env` — change `DB_CONNECTION` to `mysql`, configure MariaDB credentials
- `database/migrations/` — 5 migration files for the tables and spatial indexes
- `app/Models/Wojewodztwo.php`
- `app/Models/Kategoria.php`
- `app/Models/Obiekt.php` (with spatial scopes)
- `app/Models/Artkul.php`
- `database/seeders/DatabaseSeeder.php` — comprehensive seed data
- `tests/Feature/ObiektTest.php`
- `tests/Feature/KategoriaTest.php`
- `tests/Feature/ArtkulTest.php`

## Critical Requirements

- MariaDB must be used (not SQLite) for spatial support
- `geometry` column on `obiekty` must use `GEOMETRY` and be indexed with a `SPATIAL INDEX`
- `scopeNearby` must use `ST_Distance_Sphere` for distance calculation
- `scopeSearchByTitle` must use case-insensitive partial matching on MariaDB
- `scopeNearbyWithFallback` must try 5km then 20km
- `scopeInCategory` must include parent category and all descendants (up to 3 levels)
- Slugs auto-generated from titles with collision handling
- All Pest tests must pass

## Do NOT

- Do not install Filament, Spatie Media Library, or any other packages
- Do not create any routes or controllers
- Do not create any Blade views or Svelte components
- Do not modify any existing controllers or views

## Reference

Read the full RFC at: `.ai/RFCs/RFC-001-Database-Foundation.md`
