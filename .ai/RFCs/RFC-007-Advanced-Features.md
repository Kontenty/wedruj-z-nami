# RFC-007: Advanced Features (Nearby Objects, Print, Polish)

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "news" = _aktualności_

**Status:** Proposed  
**Complexity:** Medium  
**Predecessors:** RFC-001, RFC-005, RFC-006  
**Successors:** — (final RFC)

---

## Summary

Implement the remaining beta features: nearby objects, enhanced print layout for object detail pages, WCAG accessibility audit, performance optimization pass, and final polish. This RFC completes the beta scope defined in the PRD.

---

## Features / Requirements Addressed

- US-006: Nearby objects (within 20 km radius)
- US-009: Enhanced print layout for object detail
- WCAG 2.1 AA compliance pass
- Final responsive design polish
- Performance optimization

---

## Previous / Next

- **Builds on:** RFC-001 (PostGIS spatial queries), RFC-005 (catalog page exists), RFC-006 (object detail page exists)
- **Built by future:** — (final RFC; product is beta-complete)

---

## Technical Approach

### Nearby Objects

#### Backend: JSON API Endpoint

Create a lightweight JSON endpoint for fetching nearby objects (called via JavaScript on the object detail page):

```php
// routes/web.php
Route::get('/katalog/{slug}/nearby', [ObjectController::class, 'nearby'])->name('catalog.nearby');
```

```php
// In ObjectController
public function nearby(string $slug): JsonResponse
{
    $object = Obiekt::published()
        ->where('slug', $slug)
        ->firstOrFail();

    if (!$object->latitude || !$object->longitude) {
        return response()->json(['objects' => []]);
    }

    $nearby = Obiekt::published()
        ->where('id', '!=', $object->id)
        ->nearby($object->latitude, $object->longitude, 20)
        ->limit(3)
        ->with(['wojewodztwo'])
        ->get();

    return response()->json([
        'objects' => ObjectResource::collection($nearby),
        'has_results' => $nearby->isNotEmpty(),
    ]);
}
```

#### Frontend: Dynamic Loading on Object Detail Page

Replace the placeholder in `resources/views/objects/show.blade.php` with JavaScript-driven loading:

```blade
<section id="nearby-objects" class="mt-12">
    <h2 class="text-xl font-semibold mb-4">Nearby Objects</h2>
    <div id="nearby-container" data-slug="{{ $object->slug }}">
        <div id="nearby-loading" class="animate-pulse">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="h-48 bg-gray-200 rounded"></div>
                <div class="h-48 bg-gray-200 rounded"></div>
                <div class="h-48 bg-gray-200 rounded"></div>
            </div>
        </div>
            <div id="nearby-results" class="hidden">
                <div id="nearby-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"></div>
            </div>
        <div id="nearby-empty" class="hidden text-gray-500">
            <p>No nearby objects found.</p>
            <a href="{{ route('catalog.index') }}" class="text-primary hover:underline">
                Browse Catalog →
            </a>
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const slug = document.getElementById('nearby-container').dataset.slug;
        const loading = document.getElementById('nearby-loading');
        const results = document.getElementById('nearby-results');
        const empty = document.getElementById('nearby-empty');
        const grid = document.getElementById('nearby-grid');
        try {
            const res = await fetch(`/katalog/${slug}/nearby`);
            const data = await res.json();

            loading.classList.add('hidden');

            if (!data.has_results) {
                empty.classList.remove('hidden');
                return;
            }

            grid.innerHTML = data.objects.map(obj => `
                <a href="${obj.url}" class="group block bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                    <img src="${obj.thumbnail_url}" alt="${obj.title}" class="w-full h-40 object-cover rounded-t-lg" loading="lazy" />
                    <div class="p-3">
                        <h3 class="font-semibold group-hover:text-primary">${obj.title}</h3>
                        <p class="text-sm text-gray-500">${obj.wojewodztwo.name}</p>
                        ${obj.is_unesco ? '<span class="badge">UNESCO</span>' : ''}
                    </div>
                </a>
            `).join('');

            results.classList.remove('hidden');
        } catch (e) {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
        }
    });
</script>
@endpush
```

### Enhanced Print Layout

Build on the basic print CSS from RFC-006 with a dedicated print stylesheet:

```css
/* resources/css/print.css */

@media print {
    /* Hide everything except article content */
    header,
    footer,
    nav,
    aside,
    .print-hidden,
    #nearby-objects,
    button[onclick*='print'],
    .lightbox {
        display: none !important;
    }

    /* Reset page */
    body {
        font-size: 12pt;
        line-height: 1.5;
        color: #000;
        background: #fff;
    }

    article {
        max-width: 100%;
        padding: 0;
        margin: 0;
    }

    /* Title */
    h1 {
        font-size: 24pt;
        margin-bottom: 0.5em;
    }

    /* Metadata */
    .metadata-row {
        font-size: 10pt;
        margin-bottom: 1em;
        border-bottom: 1px solid #ccc;
        padding-bottom: 0.5em;
    }

    /* Images */
    img {
        max-width: 100%;
        height: auto;
        page-break-inside: avoid;
    }

    figure {
        margin-bottom: 1em;
    }

    /* Gallery: show inline, smaller */
    .gallery-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1em;
    }
    .gallery-grid img {
        width: 120px;
        height: 90px;
        object-fit: cover;
    }

    /* Description */
    .prose {
        font-size: 11pt;
        page-break-before: auto;
    }

    /* Practical info */
    .practical-info {
        border: 1px solid #ccc;
        padding: 12px;
        margin-bottom: 1em;
        page-break-inside: avoid;
    }

    /* Page header/footer for print */
    @page {
        margin: 2cm;
    }

    /* URL after links */
    a[href]:not([href^='javascript'])::after {
        content: ' (' attr(href) ')';
        font-size: 9pt;
        color: #666;
    }

    /* Exclude external URL display for nav links */
    nav a[href]::after,
    header a[href]::after,
    footer a[href]::after {
        content: none;
    }
}
```

Add print-specific classes to the object detail view:

```blade
{{-- Add to object detail page --}}
<section class="practical-info bg-gray-50 rounded-lg p-6 mb-8 print:border print:bg-white">
    ...
</section>
```

### WCAG 2.1 AA Accessibility Audit

Review and fix across all pages:

**Global:**

- Skip-to-content link on all pages
- Landmark regions: `<header>`, `<nav>`, `<main>`, `<footer>`
- Language attribute: `<html lang="pl">`
- Page titles are descriptive and unique

**Forms:**

- All inputs have associated labels
- Error messages linked to fields via `aria-describedby`
- Required fields marked with `aria-required` or visible indicator
- Form validation errors announced to screen readers

**Navigation:**

- Keyboard navigable header and footer
- Focus visible on all interactive elements (focus ring)
- Current page indicated in nav (`aria-current="page"`)

**Images:**

- All `<img>` have meaningful `alt` text
- Decorative images use `alt=""`

**Color:**

- Contrast ratio ≥ 4.5:1 for normal text
- Contrast ratio ≥ 3:1 for large text
- Information not conveyed by color alone (UNESCO badge has text, not just color)

**Map:**

- Map has text alternative (the card grid serves as alternative)
- Map does not trap keyboard focus
- Map controls accessible by keyboard

**Catalog filters:**

- Filter controls labeled properly
- Category accordion supports keyboard (Enter/Space to expand, arrow keys)
- Active filter changes announced via `aria-live="polite"`

**Mobile:**

- Bottom sheet traps focus while open
- Focus returns to trigger element on sheet close
- Touch targets ≥ 44×44px

### Performance Optimization

1. **Eager loading:** Ensure all list/detail pages eager-load relationships
2. **Image optimization:** Use `loading="lazy"` on below-fold images
3. **CSS:** Ensure Tailwind purges unused styles (already configured)
4. **Map:** Consider marker clustering if object count exceeds ~100
5. **Caching:** Cache homepage queries (latest objects, latest news) with short TTL
6. **Database:** Verify spatial indexes are used (EXPLAIN on nearby queries)

### Contact Form Enhancement

### Final Polish

1. **Consistent spacing:** Ensure all pages use consistent Tailwind spacing tokens
2. **Typography:** Verify heading hierarchy and font sizes across pages
3. **Color consistency:** Primary color used consistently for CTAs and links
4. **Loading states:** All async operations have loading indicators
5. **Error states:** All error conditions have user-friendly messages
6. **Empty states:** All empty conditions have helpful guidance
7. **404 page:** Custom 404 page with navigation back to catalog/home

---

## Data Flow

```
[Object Detail Page]
    │
    ├── Initial render: object data, images, practical info
    │   (from ObjectController::show, RFC-006)
    │
    └── AJAX: /katalog/{slug}/nearby
        │
        └── ObjectController::nearby
            ├── Obiekt::nearby($lat, $lng, 20)
            └── JSON response with objects
```

---

## Acceptance Criteria

### Nearby Objects

- [ ] `/katalog/{slug}/nearby` returns JSON with objects
- [ ] Maximum 3 nearby objects returned
- [ ] Current object excluded from results
- [ ] Objects ordered by distance
- [ ] Results show on object detail page dynamically
- [ ] Loading skeleton visible during fetch
- [ ] Empty state with catalog link when no nearby objects at 20km

### Print Layout

- [ ] Print CSS hides header, footer, nav, nearby objects section
- [ ] Print preserves title, metadata, images, description, practical info
- [ ] Print layout readable on A4
- [ ] Print button triggers browser print dialog
- [ ] External link URLs displayed in print version
- [ ] Images scale to fit print width
- [ ] Page break handling prevents orphaned sections

### Accessibility

- [ ] Skip-to-content link on all pages
- [ ] All images have alt text
- [ ] All form inputs have labels
- [ ] Color contrast meets WCAG AA
- [ ] Keyboard navigation works on all interactive elements
- [ ] Focus states visible
- [ ] Map does not trap keyboard focus
- [ ] Bottom sheet traps focus correctly
- [ ] Object type accordion keyboard accessible
- [ ] Screen reader announces filter changes

### Performance

- [ ] No N+1 queries on any page
- [ ] Images lazy-loaded below the fold
- [ ] Homepage queries cached
- [ ] Spatial queries use GiST index

### Final Polish

- [ ] Custom 404 page
- [ ] Consistent design across all pages
- [ ] Responsive on all breakpoints
- [ ] All error/empty/loading states handled

---

## Testing Strategy

### Feature Tests (Pest)

**Nearby objects:**

- `/katalog/{slug}/nearby` returns JSON
- Returns objects within 20km
- Excludes current object from results
- Returns empty for object without coordinates
- Returns 404 for non-existent object

**Print:**

- Print CSS present in asset output
- Object detail page includes print stylesheet

**Accessibility:**

- All pages have skip-to-content link
- All images have alt attributes
- Form inputs have labels (automated check)

**404:**

- Custom 404 page renders for non-existent routes

---

## Error Handling

- Nearby objects fetch failure: show empty state with catalog link
- Missing coordinates on object: skip nearby section entirely
- Map initialization error: graceful degradation, card grid still works

---

## Performance Considerations

- Nearby objects loaded via AJAX, not blocking initial page render
- Spatial query uses GiST index for O(log n) performance
- Limit nearby results to 3 to keep response small
- Cache homepage queries with `Cache::remember()` (5 minute TTL)

---

## Security Considerations

- Nearby endpoint only returns published objects
- No user input reflected without escaping
- No user input reflected without escaping

---

## Implementation Constraints

- Beta scope: no favorites, reviews, comments, tags, or advanced news features
- Polish language only
- No public user accounts
- No complex CMS workflows
- Data and images from team's own database only
