# Implementation Prompt: RFC-006

**Title:** Object Detail Page  
**ID:** RFC-006  
**Brief Description:** Build the `/katalog/{object:slug}` Inertia/Svelte page — a document-like, reference-oriented view with title, lead, location map, description, image gallery, practical info, metadata, print button, and nearby objects placeholder.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "object type" = *typ obiektu*

---

You are implementing RFC-006 for the Kanon project. This RFC creates the individual object detail page that users reach from the catalog.

## Prerequisites

- RFC-001 must be completed (models with relationships)
- RFC-002 must be completed (media URLs on models)
- RFC-005 must be completed (catalog links to object detail pages)

## What to Build

1. **ObjectController::show** — loads published object by slug via route model binding, prepares image data from Spatie, prepares GeoJSON for location map, returns Inertia response
2. **Svelte page component** (`Catalog/Show.svelte`) — title, metadata row (voivodeship, locality, object types, UNESCO), lead, location map, main image, gallery with lightbox, Markdown description, practical info, print button, nearby objects placeholder
3. **Route** — `/katalog/{object:slug}` with route model binding on slug (requires `getRouteKeyName()` on SightseeingObject model)
4. **Basic print CSS** — hides header/footer/nav, preserves content
5. **ImageGallery.svelte** — gallery grid + lightbox for image viewing
6. **ObjectMap.svelte** — small MapLibre map showing object point marker or polygon
7. **Write Pest tests** for object detail page

## Key Files to Create/Modify

- `routes/web.php` — add `/katalog/{object:slug}` route
- `app/Http/Controllers/ObjectController.php`
- `app/Http/Resources/SightseeingObjectResource.php`
- `resources/js/pages/Catalog/Show.svelte`
- `resources/js/pages/Catalog/ObjectMap.svelte`
- `resources/js/pages/Catalog/ImageGallery.svelte`
- `resources/js/pages/Catalog/PracticalInfo.svelte`
- `resources/js/pages/Catalog/NearbyObjects.svelte` (stub)
- `resources/css/print.css` (basic print styles)
- `tests/Feature/ObjectDetailTest.php`

## Critical Requirements

- `/katalog/{object:slug}` renders Inertia Svelte page for published objects only
- `/katalog/{nonexistent}` returns 404
- `/katalog/{object:slug}` for unpublished objects returns 404
- Page displays: title, voivodeship name, locality, object type badges, UNESCO badge
- Lead (short description) displayed when present
- Location map shows object marker (point) or polygon, centered on coordinates via MapLibre/Svelte
- Main image displayed prominently (first in media order)
- Gallery shows thumbnail grid when > 1 image exists
- Gallery thumbnail click opens lightbox with full image
- Lightbox closes on Escape key and backdrop click
- Description rendered from Markdown to HTML (server-side via `Str::markdown()`)
- Practical info section: shown only when data exists (opening hours, prices, website)
- External website links open in new tab with `rel="noopener"`
- Print button triggers `window.print()`
- Basic print CSS hides interactive elements
- Back link to catalog
- Nearby objects section container present (stub, populated by RFC-007)
- Page responsive on mobile and desktop

## Do NOT

- Do not implement nearby objects loading (that's RFC-007)
- Do not enhance the print layout (that's RFC-007)
- Do not modify the catalog page or filters
- Do not create the homepage or article pages
- Do not create Blade views for this page (it's Inertia/Svelte)

## Reference

Read the full RFC at: `.ai/RFCs/RFC-006-Object-Detail.md`
