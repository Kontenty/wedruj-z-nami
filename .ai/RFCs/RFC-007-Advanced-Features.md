# RFC-007: Advanced Features (Nearby Objects, Print, Polish)

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "news" = _aktualności_

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** RFC-001, RFC-005, RFC-006  
**Successors:** — (final RFC)

---

## Summary

Complete the remaining beta/PRD scope: nearby objects on the object detail page, missing practical accessibility information, polygon-safe detail maps, print polish, critical public accessibility fixes, homepage caching, and a custom 404 page.

This RFC is an implementation checklist against the actual current codebase. Current object detail routing is handled by `CatalogController::show`, resources are `ObjectDetailResource` and `ObjectResource`, and `resources/js/pages/Catalog/NearbyObjects.svelte` currently exists as a loading stub.

---

## Features / Requirements Addressed

- US-006: Object detail page, including up to 3 nearest published objects within 20 km.
- PRD 5.3: Optional practical information, including opening hours, ticket prices, and accessibility.
- PRD 6: Polygon support and centroid-based nearby calculation.
- PRD 8: Responsive UX, WCAG level A target, performance, SEO-friendly error handling.
- Final beta polish: print layout, caching, empty states, custom 404.

---

## Implementation Checklist

### 1. Backend: Nearby Objects

**Files:**

- `app/Models/SightseeingObject.php`
- `app/Http/Controllers/CatalogController.php`
- `app/Http/Resources/ObjectResource.php`
- `tests/Feature/CatalogShowTest.php`

**Plan:**

- [ ] Refactor/verify `SightseeingObject::nearby()` so it supports PRD geometry rules:
  - origin point objects use their point geometry/coordinates;
  - origin polygon objects use `ST_Centroid(geometry)`;
  - candidate polygon objects use `ST_Centroid(geometry)`;
  - candidate point objects use their point geometry;
  - only published objects are returned;
  - current object is excluded;
  - results are within 20 km;
  - results are ordered by distance ascending;
  - at most 3 objects are returned.
- [ ] Do not rely only on `latitude` / `longitude` for polygon origin objects. Use DB geometry/centroid as the source of truth for nearby calculations.
- [ ] Keep nearby objects server-side as Inertia props. No separate AJAX endpoint is needed.
- [ ] In `CatalogController::show`, eager-load relationships needed by cards and return:

```php
'nearby' => ObjectResource::collection($nearby),
```

- [ ] Ensure `ObjectResource` supports frontend linking. Either:
  - add `url => route('catalog.show', $this->slug)`, or
  - build detail URLs in Svelte using Wayfinder route helpers and `slug`.
- [ ] Avoid N+1 queries for nearby cards by eager-loading `voivodeship`, `objectTypes`, and media if thumbnails are used.

**Tests:**

- [ ] Object detail response includes `nearby` prop.
- [ ] Returns up to 3 nearby objects.
- [ ] Excludes the current object.
- [ ] Excludes unpublished/draft objects.
- [ ] Excludes objects outside 20 km.
- [ ] Orders objects by distance.
- [ ] Uses polygon centroid for polygon origin objects.
- [ ] Uses polygon centroid for polygon candidate objects.
- [ ] Returns an empty collection when no nearby objects exist.

---

### 2. Frontend: Nearby Objects UI

**Files:**

- `resources/js/pages/Catalog/Show.svelte`
- `resources/js/pages/Catalog/NearbyObjects.svelte`

**Plan:**

- [ ] Update `Show.svelte` props:

```svelte
let { object, images, geojson, nearby } = $props();
```

- [ ] Replace the current stub call:

```svelte
<NearbyObjects slug={object.slug} />
```

with:

```svelte
<NearbyObjects {nearby} />
```

- [ ] Replace `NearbyObjects.svelte` loading stub with a real Polish UI.
- [ ] Render each nearby object card with:
  - image/thumbnail;
  - title;
  - voivodeship;
  - UNESCO badge when applicable;
  - link to object detail.
- [ ] Add `loading="lazy"` to below-fold nearby images.
- [ ] Show a helpful empty state and catalog link when no nearby objects are found.

---

### 3. PRD Gap: Practical Accessibility Information

The PRD requires object detail pages to show optional accessibility information. The current detail resource/UI omits it.

**Files:**

- `app/Http/Resources/ObjectDetailResource.php`
- `resources/js/pages/Catalog/Show.svelte`
- `resources/js/pages/Catalog/PracticalInfo.svelte`
- `tests/Feature/CatalogShowTest.php`

**Plan:**

- [ ] Add `accessibility` to `ObjectDetailResource`.
- [ ] Pass `accessibility={object.accessibility}` to `PracticalInfo`.
- [ ] Render accessibility information when present.
- [ ] Keep practical-info section hidden or compact when all practical fields are empty.

**Tests:**

- [ ] Object detail Inertia props include `object.accessibility`.
- [ ] Existing practical-info tests, if any, are updated for the new field.

---

### 4. PRD Gap: Polygon-Safe Detail Map

Current detail UI only renders the map when `object.latitude && object.longitude`. Polygon objects must display their full area and fit the viewport, even if coordinates are missing.

**Files:**

- `resources/js/pages/Catalog/Show.svelte`
- `resources/js/pages/Catalog/ObjectMap.svelte`
- `tests/Feature/CatalogShowTest.php`

**Plan:**

- [ ] Render `ObjectMap` when either coordinates exist or `geojson` exists.
- [ ] Update `ObjectMap.svelte` to support polygon-only geometry.
- [ ] For polygons/multipolygons:
  - parse `geojson`;
  - render fill and outline;
  - fit bounds to geometry.
- [ ] For points:
  - render marker and popup as today.
- [ ] Ensure map initialization failure degrades gracefully and does not break page content.

**Tests:**

- [ ] Polygon object detail response includes `geojson`.
- [ ] Existing polygon detail test remains green.

---

### 5. Print Layout Polish

Current print styles live in `resources/css/app.css`. Extend the existing `@media print` block instead of adding a separate unreferenced stylesheet.

**File:**

- `resources/css/app.css`

**Plan:**

- [ ] Hide non-print UI:
  - header;
  - footer;
  - nav;
  - filter/sidebar UI;
  - map controls/interactive map if needed;
  - nearby section;
  - print button;
  - other `.print-hidden` elements.
- [ ] Preserve printable content:
  - title;
  - metadata badges;
  - images/gallery;
  - lead;
  - description;
  - practical info;
  - source/update data where available.
- [ ] Add A4-readable rules:
  - `@page` margin;
  - print font sizes;
  - image max-width;
  - avoid page breaks inside figures/cards/practical-info;
  - readable link URL display for external links.
- [ ] Keep the existing `window.print()` button behavior.

---

### 6. Critical Public Accessibility Fixes

Target critical WCAG-level public issues only: public Blade pages plus catalog/detail Inertia pages. This does not attempt a full CMS/auth audit.

**Files:**

- `resources/views/layouts/public.blade.php`
- `resources/views/app.blade.php`
- `resources/js/pages/Catalog/Index.svelte`
- `resources/js/pages/Catalog/Show.svelte`
- `resources/js/pages/Catalog/CatalogMap.svelte`
- `resources/js/pages/Catalog/ObjectMap.svelte`
- catalog filter components as needed

**Plan:**

- [ ] Add a skip-to-content link to the public Blade layout.
- [ ] Add a skip-to-content link or equivalent in the Inertia shell.
- [ ] Ensure public main content has a stable `id="main-content"` target.
- [ ] Ensure visible focus states on public links, buttons, and form controls.
- [ ] Ensure filter controls have labels or accessible names.
- [ ] Keep/verify `aria-live="polite"` for catalog result count.
- [ ] Add accessible labels or text alternatives for map regions.
- [ ] Ensure the object grid/list remains the keyboard-accessible alternative to map interaction.
- [ ] Ensure all public images have meaningful `alt` text, or `alt=""` when decorative.

---

### 7. Homepage Performance Caching

**File:**

- `app/Http/Controllers/HomeController.php`

**Plan:**

- [ ] Cache homepage latest objects query for a short TTL, e.g. 5 minutes.
- [ ] Cache homepage latest news query for a short TTL, e.g. 5 minutes.
- [ ] Keep eager-loading for latest objects.
- [ ] Accept TTL-based freshness for beta unless explicit cache invalidation is later required.

**Tests:**

- [ ] Homepage still renders latest objects/news.
- [ ] Existing `HomePageTest` remains green.

---

### 8. Custom 404 Page

**File:**

- `resources/views/errors/404.blade.php`

**Plan:**

- [ ] Add a Polish custom 404 page.
- [ ] Match public visual style.
- [ ] Include links to:
  - homepage;
  - catalog;
  - news.
- [ ] Keep page lightweight and accessible.

**Tests:**

- [ ] Nonexistent route returns 404 with custom page content.

---

## Data Flow

```text
[Object Detail Page — Inertia/Svelte]
    │
    ├── CatalogController::show
    │   ├── loads published SightseeingObject with voivodeship, objectTypes, media, geojson
    │   ├── computes nearby from DB geometry
    │   │   ├── point origin: point geometry/coordinates
    │   │   └── polygon origin: ST_Centroid(geometry)
    │   └── returns ObjectDetailResource + ObjectResource::collection($nearby)
    │
    └── Inertia::render('Catalog/Show', [
            object, images, geojson, nearby
        ])
        └── Svelte renders map, gallery, practical info, print button, nearby cards
```

---

## Acceptance Criteria

### Nearby Objects

- [ ] Up to 3 nearest published objects within 20 km shown on detail page.
- [ ] Current object excluded from results.
- [ ] Draft/unpublished objects excluded.
- [ ] Objects ordered by distance ascending.
- [ ] Polygon origin objects use DB centroid for nearby calculation.
- [ ] Polygon candidate objects use DB centroid for nearby calculation.
- [ ] Nearby objects loaded as Inertia props; no separate AJAX endpoint.
- [ ] Empty state with catalog link shown when no nearby objects exist.

### Object Detail PRD Completion

- [ ] Accessibility practical information is included in detail props.
- [ ] Accessibility practical information renders when present.
- [ ] Polygon detail maps render full geometry and fit viewport.
- [ ] Point detail maps continue to render marker/popup.

### Print Layout

- [ ] Print CSS hides header, footer, nav, nearby objects, and print button.
- [ ] Print preserves title, metadata, images, lead, description, and practical info.
- [ ] Print layout is readable on A4.
- [ ] External link URLs display where useful.
- [ ] Images scale to fit print width.
- [ ] Page-break rules prevent orphaned sections where feasible.

### Critical Accessibility

- [ ] Skip-to-content link exists on public Blade and Inertia-rendered pages.
- [ ] Main content has a stable skip-link target.
- [ ] Public form/filter controls have accessible names.
- [ ] Focus states are visible on public interactive elements.
- [ ] Map sections have accessible labels/text alternatives.
- [ ] Catalog result changes are announced with `aria-live="polite"`.
- [ ] Public images have appropriate alt text.

### Performance / Polish

- [ ] No obvious N+1 queries on object detail, catalog, homepage.
- [ ] Below-fold images lazy-load.
- [ ] Homepage latest objects/news queries are cached.
- [ ] Custom Polish 404 page exists.
- [ ] Existing responsive layouts remain usable across mobile/tablet/desktop.

---

## Testing Strategy

### Feature Tests (Pest)

**Nearby objects:**

- object detail response includes `nearby` prop;
- max 3 nearby objects;
- excludes current object;
- excludes unpublished objects;
- excludes objects outside 20 km;
- orders by distance;
- polygon origin uses centroid;
- polygon candidates use centroid;
- empty collection when no nearby objects exist.

**Object detail completion:**

- object detail includes `accessibility`;
- polygon object detail includes `geojson`;
- unpublished object still returns 404;
- nonexistent slug still returns 404.

**Homepage / 404:**

- homepage still renders latest objects/news;
- nonexistent route renders custom 404 content.

### Frontend Verification

- Run the project frontend build/type check after Svelte/CSS changes.
- Manually verify print preview for an object detail page.
- Manually verify keyboard access to catalog filters and nearby links.

---

## Error Handling

- Nearby objects: return an empty collection when no valid nearby objects exist.
- Missing or invalid geometry: do not crash object detail page; return empty nearby collection if distance cannot be computed.
- Map initialization error: page content remains readable and usable.
- Missing images: use existing placeholder image behavior.

---

## Performance Considerations

- Nearby objects are loaded server-side with the detail page.
- Nearby queries are limited to 3 returned records.
- Eager-load relationships used by object/detail/nearby cards.
- Use existing `SPATIAL INDEX` on `geometry`; verify query plans if performance is poor.
- Do not claim guaranteed O(log n) spatial performance for distance expressions over centroids; treat EXPLAIN verification as an optimization check.
- Cache homepage queries with a short TTL.

---

## Security Considerations

- Nearby objects include only published records.
- Do not expose draft/unpublished object data in nearby props.
- Continue escaping user-visible strings in Svelte/Blade.
- Existing markdown-rendered descriptions should remain constrained to trusted/editorial content expectations.

---

## Implementation Constraints

- Polish language only.
- No public user accounts.
- No favorites, reviews, comments, route planning, or personalization.
- No advanced news/blog features beyond existing lightweight news.
- Do not introduce new dependencies without approval.
- Prefer existing structure and components over new base directories.

---

## Suggested Implementation Order

1. Backend nearby query/resource/controller changes.
2. Nearby feature tests.
3. Svelte nearby UI wiring.
4. Practical `accessibility` field fix.
5. Polygon-safe detail map fix.
6. Print CSS polish.
7. Critical public accessibility fixes.
8. Homepage caching.
9. Custom 404 page.
10. Focused test run, Pint for PHP changes, frontend build/type check.
