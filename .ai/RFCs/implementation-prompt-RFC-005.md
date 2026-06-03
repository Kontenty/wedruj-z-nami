# Implementation Prompt: RFC-005

**Title:** Interactive Catalog (Inertia + Svelte)  
**ID:** RFC-005  
**Brief Description:** Build the `/katalog` page with MapLibre map, filter sidebar, partial title search, active filter chips, map/list synchronization, and responsive card grid using Inertia v3 + Svelte 5.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "object type" = *typ obiektu*

---

You are implementing RFC-005 for the Kanon project. This is the interactive public catalog browsing experience.

## Prerequisites

- RFC-001 must be completed (models, scopes, MariaDB spatial support)
- RFC-002 must be completed (media URLs on models)

## What to Build

1. **CatalogController** — serves Inertia page with filtered/paginated list objects, all filtered map objects, object type tree, and voivodeships list
2. **ObjectResource** and **ObjectTypeResource** — resources for Inertia props
3. **Catalog/Index.svelte** — main page component with state management
4. **FilterSidebar.svelte** — desktop filter panel (search, voivodeship select, object type accordion, UNESCO toggle)
5. **MobileFilterSheet.svelte** — mobile bottom sheet with local draft state, `Zastosuj`, and `Wyczyść`
6. **SearchBar.svelte** — debounced title search input
7. **ActiveFilterChips.svelte** — shows active filters with remove buttons
8. **CatalogMap.svelte** — MapLibre map with point and polygon layers
9. **MapPopup.svelte** — popup content without detail-page links
10. **ObjectGrid.svelte** + **ObjectCard.svelte** — paginated card grid results
11. **MobileViewToggle.svelte** — `Mapa` | `Lista` segmented control
12. **EmptyState.svelte** — no results message
13. **MapLibre installation** via `npm install maplibre-gl`
14. **Backend Pest tests** for CatalogController, ObjectResource, and ObjectTypeResource

## Key Files to Create/Modify

- `routes/web.php` — add `/katalog` route named `catalog.index`
- `app/Http/Controllers/CatalogController.php`
- `app/Http/Resources/ObjectResource.php`
- `app/Http/Resources/ObjectTypeResource.php`
- `resources/js/pages/Catalog/Index.svelte`
- `resources/js/pages/Catalog/FilterSidebar.svelte`
- `resources/js/pages/Catalog/MobileFilterSheet.svelte`
- `resources/js/pages/Catalog/SearchBar.svelte`
- `resources/js/pages/Catalog/ActiveFilterChips.svelte`
- `resources/js/pages/Catalog/CatalogMap.svelte`
- `resources/js/pages/Catalog/MapPopup.svelte`
- `resources/js/pages/Catalog/ObjectGrid.svelte`
- `resources/js/pages/Catalog/ObjectCard.svelte`
- `resources/js/pages/Catalog/MobileViewToggle.svelte`
- `resources/js/pages/Catalog/EmptyState.svelte`
- `tests/Feature/CatalogTest.php`

## Critical Requirements

- Use `objectTypes` everywhere; do not use `categories` naming.
- Use `voivodeship` in resource/frontend object data; frontend reads `object.voivodeship.name`, not `object.wojewodztwo.name`.
- `objects` prop is the paginated list/card results (24 per page).
- `mapObjects` prop is **all filtered published objects** with coordinates and/or GeoJSON (expected max ~500). Each map object must include at least `id`, `title`, `slug`, `latitude`, `longitude`, `geojson`, `thumbnail_url`, and `voivodeship`.
- Map uses OpenFreeMap Liberty style: `https://tiles.openfreemap.org/styles/liberty`.
- Map renders point markers from `latitude`/`longitude`.
- Map renders polygon fill/outline from the spatial DB column named `geometry`, exposed as GeoJSON.
- If an object has both coordinates and polygon GeoJSON, render both the point marker and the polygon.
- Clustering applies only to point features. Use separate point and polygon sources/layers.
- Popup works for point and polygon clicks and shows thumbnail, title, and voivodeship. It must not link to a detail page.
- Desktop map/list sync: card hover highlights the matching map feature; map feature click opens popup and scrolls the corresponding card into view.
- Do not add `route('catalog.show', ...)` or object detail URLs in ObjectResource; RFC-006 owns detail pages.
- Missing `thumbnail_url` uses an existing static placeholder image from `/public/images/` in both cards and popups. Check existing files and choose the most appropriate object/image placeholder before adding a new asset.
- Voivodeship filter: single select using slug URL param `wojewodztwo`.
- Object type filter: hierarchical accordion, 3 levels, URL param `objectType` containing object type ID. Filtering by a parent type includes descendants; use an existing model relationship/scope if present, otherwise collect descendant IDs and filter the pivot with `whereIn`.
- UNESCO toggle: URL param `unesco=true` when enabled. Missing `unesco` or `unesco=false` means no UNESCO filter.
- Search: debounced partial phrase title search only, case-insensitive MariaDB `LIKE`. Do not describe or implement fuzzy search.
- Canonical filter URL example: `/katalog?q=zamek&wojewodztwo=malopolskie&objectType=12&unesco=true`.
- Filter changes must reset/omit `page`; pagination links must preserve current filter query params.
- Filter changes must use Inertia partial reloads with `only: ['objects', 'mapObjects', 'filters']`.
- Use generated Wayfinder route helper for `/katalog` frontend router calls; do not hardcode `'/katalog'` in Svelte. Expected import: `import { index as catalogIndex } from '@/routes/catalog';`.
- Regenerate Wayfinder after route changes if needed.
- All visible UI copy must be Polish: `Województwo`, `Typ obiektu`, `Tylko UNESCO`, `Wyczyść filtry`, `Nie znaleziono obiektów...`, `Mapa`, `Lista`, `Filtry`, `Zastosuj`.
- Mobile filter sheet uses local draft state. Opening the sheet initializes draft state from current `filters`; closing without applying discards draft changes. Changes apply only when the user taps `Zastosuj`; include `Wyczyść`.
- URL query params persist filter state (shareable, browser nav).
- Desktop: sidebar + map + card grid split layout.
- Mobile: segmented control (`Mapa` | `Lista`), default is map.
- Loading state: skeleton cards.
- Empty state: Polish message + clear-filters action.
- Unpublished objects excluded from both `objects` and `mapObjects`.
- Use Svelte 5 runes syntax: `$props`, `$state`, `$derived`, `$effect`; do not use Svelte 4 `export let` or `on:` event syntax.
- Clean up MapLibre map/listeners when `CatalogMap.svelte` unmounts.

## Geometry Query Guidance

- The source column is `geometry` on the sightseeing objects table.
- Select/generate GeoJSON from the column in SQL, e.g. with `ST_AsGeoJSON(geometry) AS geojson`, as part of the query.
- Avoid per-row raw SQL with bound geometry such as `ST_AsGeoJSON(?)`.
- Frontend must safely parse `geojson`; invalid/unsupported geometry should skip polygon rendering without breaking the map. If coordinates exist, the point marker should still render.

## Do NOT

- Do not create the object detail page (that's RFC-006).
- Do not link catalog cards/popups to detail pages yet.
- Do not implement nearby objects (that's RFC-007).
- Do not modify Filament CMS resources.
- Do not create Blade views for the catalog (it's Inertia/Svelte).
- Do not add frontend/browser/component tests in RFC-005.
- Do not add `@types/maplibre-gl` unless the project conventions or TypeScript compiler require it.
- Do not skip version-specific docs lookup before implementation; use Laravel Boost `search-docs` for Inertia v3, Svelte, Wayfinder, API resources, and Pest testing.

## Testing Scope

Backend Pest tests only. Organize tests in separate files:

- `tests/Feature/CatalogTest.php` for `CatalogController`
  - `/katalog` returns Inertia response with `objects`, `mapObjects`, `filters`, `objectTypes`, and `voivodeships`
  - filters by voivodeship slug
  - filters by object type ID including descendants
  - filters by UNESCO when `unesco=true`
  - searches by title with case-insensitive partial phrase matching
  - combines filters correctly
  - excludes unpublished objects from list and map props
  - paginates `objects` at 24 per page
  - keeps `mapObjects` unpaginated for all filtered results
- `tests/Unit/Http/Resources/ObjectResourceTest.php` for `ObjectResource`
  - exposes `id`, `title`, `slug`, `description`, `latitude`, `longitude`, `is_unesco`, `thumbnail_url`, `primary_image_url`, `voivodeship`, `objectTypes`, `geojson`, and no detail URL
  - uses an existing `/public/images/` placeholder image when `thumbnail_url` is missing
- `tests/Unit/Http/Resources/ObjectTypeResourceTest.php` for `ObjectTypeResource`
  - exposes recursive children as `children`

## Verification

- Run `npm install maplibre-gl`.
- If route files changed and generated routes are not updated automatically, run `php artisan wayfinder:generate --no-interaction`.
- Run targeted Pest tests for RFC-005.
- Run `vendor/bin/pint --dirty --format agent` after PHP edits.

## Reference

Read the full RFC at: `.ai/RFCs/RFC-005-Interactive-Catalog.md`
