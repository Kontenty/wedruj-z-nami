# Implementation Prompt: RFC-001

**Title:** Database Foundation & Core Models  
**ID:** RFC-001  
**Brief Description:** Establish MariaDB 10.11 database with PRD-aligned domain models, migrations, relationships, scopes, factories, seeders, and tests.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "object type" = *typ obiektu*; "news" = *aktualności*

---

You are implementing RFC-001 for the Kanon project — a Polish sightseeing objects catalog. This is the **foundation RFC** that all subsequent RFCs depend on.

This implementation must follow `.ai/PRD.md`. If the RFC and PRD conflict, the PRD wins.

This RFC only implements the database/model foundation. Do not implement public pages, CMS screens, maps, media upload, authentication, authorization, routes, controllers, Blade views, or Svelte components in this milestone.

## What to Build

1. **Switch database from SQLite to MariaDB**
2. **Create migrations** for: `voivodeships`, `object_types`, `sightseeing_objects`, `object_object_type`, `articles`
3. **Create Eloquent models** with relationships, scopes, and slug generation
4. **Create factories** for all new models
5. **Seed the database** with 16 Polish voivodeships, sample object types, sample sightseeing objects (including UNESCO and polygon geometry), and sample news entries
6. **Defer search integration** to a later RFC; do not add Scout or another search package in this milestone
7. **Write Pest tests** for all models, relationships, factories, seed data, and scopes

## Key Files to Create/Modify

- `.env` — change local `DB_CONNECTION` to `mysql`, configure MariaDB credentials without committing secrets
- `.env.example` — update safe MariaDB defaults if needed
- `database/migrations/` — 5 migration files for the tables and spatial indexes
- `app/Models/Voivodeship.php`
- `app/Models/ObjectType.php` (technical table/model for PRD object types)
- `app/Models/SightseeingObject.php` (with spatial scopes)
- `app/Models/Article.php` (technical model for PRD news entries)
- `database/factories/VoivodeshipFactory.php`
- `database/factories/ObjectTypeFactory.php`
- `database/factories/SightseeingObjectFactory.php`
- `database/factories/ArticleFactory.php`
- `database/seeders/DatabaseSeeder.php` — comprehensive seed data
- `tests/Feature/SightseeingObjectTest.php`
- `tests/Feature/ObjectTypeTest.php`
- `tests/Feature/VoivodeshipTest.php`
- `tests/Feature/ArticleTest.php`

## Critical Requirements

- MariaDB must be used (not SQLite) for spatial support
- Use Laravel/MariaDB-compatible migrations. Treat SQL snippets in the RFC as conceptual only; do not copy PostgreSQL-only syntax such as `bigserial`, partial indexes with `WHERE`, or `NULLS LAST`
- `geometry` column on `sightseeing_objects` must use `GEOMETRY` and be indexed with a `SPATIAL INDEX`
- `sightseeing_objects` must support both point and polygon geometries
- For polygon objects, nearby distance calculations must use the polygon centroid
- `scopeNearby` must use `ST_Distance_Sphere` for distance calculation and must parameterize raw SQL values
- Nearby objects must follow the PRD: return up to 3 nearest published objects within 20 km; if fewer than 3 exist, return only available objects; if none exist, return an empty result
- `scopeSearchByTitle` must use case-insensitive partial matching on MariaDB
- `scopeInCategory` must filter by PRD object type and include the selected type plus descendants up to 3 levels
- Slugs must be auto-generated with collision handling: from `name` for `Voivodeship` and `ObjectType`, from `title` for `SightseeingObject` and `Article`
- Sightseeing objects must include PRD foundation fields: title, slug, lead/short description, full description, object type relationship, voivodeship relationship, locality, UNESCO flag, geometry, opening hours, ticket prices, accessibility, data source, status, publication timestamp, and last source/update metadata
- News entries must include PRD foundation fields: title, slug, body, publication status (`draft`, `published`), and publication timestamp
- Publication filtering must use a `published` boolean and `published_at` timestamp
- All Pest tests must pass

## Do NOT

- Do not install Filament, Spatie Media Library, or any other packages
- Do not create any routes or controllers
- Do not create any Blade views or Svelte components
- Do not modify any existing controllers or views

## Reference

Read the full RFC at: `.ai/RFCs/RFC-001-Database-Foundation.md`
