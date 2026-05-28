# Implementation Prompt: RFC-005

**Title:** Interactive Catalog (Inertia + Svelte)  
**ID:** RFC-005  
**Brief Description:** Build the `/katalog` page with Leaflet map, filter sidebar, fuzzy search, active filter chips, map/list synchronization, and responsive card grid using Inertia v3 + Svelte 5.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "category" = *kategoria*

---

You are implementing RFC-005 for the Kanon project. This is the most interactive part of the application — the map-first catalog browsing experience.

## Prerequisites

- RFC-001 must be completed (models, scopes, PostGIS)
- RFC-002 must be completed (media URLs on models)

## What to Build

1. **CatalogController** — serves Inertia page with filtered/paginated objects, categories tree, voivodeships list
2. **ObjectResource** and **CategoryResource** — API resources for Inertia props
3. **Catalog/Index.svelte** — main page component with state management
4. **FilterSidebar.svelte** — desktop filter panel (search, voivodeship select, category accordion, UNESCO toggle)
5. **MobileFilterSheet.svelte** — mobile bottom sheet with same filters
6. **SearchBar.svelte** — debounced search input
7. **ActiveFilterChips.svelte** — shows active filters with remove buttons
8. **CatalogMap.svelte** — Leaflet map with markers and polygons
9. **MapPopup.svelte** — marker popup content
10. **ObjectGrid.svelte** + **ObjectCard.svelte** — card grid results
11. **MobileViewToggle.svelte** — Map | List segmented control
12. **EmptyState.svelte** — no results message
13. **Leaflet installation** via npm
14. **Write Pest tests** for CatalogController filter combinations

## Key Files to Create/Modify

- `routes/web.php` — add `/katalog` route
- `app/Http/Controllers/CatalogController.php`
- `app/Http/Resources/ObjectResource.php`
- `app/Http/Resources/CategoryResource.php`
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

- Map displays all published objects as Leaflet markers at correct coordinates
- Simplified polygons rendered for objects with PostGIS geometry
- Map popup shows thumbnail, title, voivodeship, link to detail
- Voivodeship filter: single select, updates URL and results
- Category filter: hierarchical accordion, 3 levels, selects parent + descendants
- UNESCO toggle: filters to UNESCO-only objects
- Search: debounced fuzzy search by title (ilike on PostgreSQL)
- Active filter chips: display current filters, remove button on each
- "Clear Filters" clears all filters
- Result count displayed and updates
- Mobile: segmented control (Map | List), default is Map
- Mobile: Filters button opens bottom sheet
- URL query params persist all filter state (shareable, browser nav)
- Partial reloads via Inertia (`only: ['objects']`)
- Desktop: sidebar + map + card grid split layout
- Loading state: skeleton cards
- Empty state: message + clear-filters action
- Unpublished objects excluded from results

## Do NOT

- Do not create the object detail page (that's RFC-006)
- Do not implement nearby objects (that's RFC-007)
- Do not modify Filament CMS resources
- Do not create Blade views for the catalog (it's Inertia/Svelte)

## Reference

Read the full RFC at: `.ai/RFCs/RFC-005-Interactive-Catalog.md`
