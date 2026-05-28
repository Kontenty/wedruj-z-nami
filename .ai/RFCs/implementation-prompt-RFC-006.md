# Implementation Prompt: RFC-006

**Title:** Object Detail Page  
**ID:** RFC-006  
**Brief Description:** Build the `/obiekty/{slug}` Blade page — a document-like, reference-oriented view with title, description, image gallery, practical info, metadata, print button, and nearby objects placeholder.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "category" = *kategoria*

---

You are implementing RFC-006 for the Kanon project. This RFC creates the individual object detail page that users reach from the catalog.

## Prerequisites

- RFC-001 must be completed (models with relationships)
- RFC-002 must be completed (media URLs on models)
- RFC-005 must be completed (catalog links to object detail pages)

## What to Build

1. **ObjectController::show** — loads published object by slug with relationships, prepares image data from Spatie
2. **Object detail Blade view** — title, metadata row, main image, gallery, Markdown description, practical info, print button, nearby objects placeholder
3. **Route** — `/obiekty/{slug:slug}` with route model binding on slug
4. **Basic print CSS** — hides header/footer/nav, preserves content
5. **Simple lightbox** — for gallery image viewing (JS, no library)
6. **Write Pest tests** for object detail page

## Key Files to Create/Modify

- `routes/web.php` — add `/obiekty/{slug}` route
- `app/Http/Controllers/ObjectController.php`
- `resources/views/objects/show.blade.php`
- `resources/css/print.css` (basic print styles)
- `tests/Feature/ObjectDetailTest.php`

## Critical Requirements

- `/obiekty/{slug}` renders for published objects only
- `/obiekty/{nonexistent}` returns 404
- `/obiekty/{slug}` for unpublished objects returns 404
- Page displays: title, voivodeship name, category badges, UNESCO badge
- Main image displayed prominently (first in media order)
- Gallery shows thumbnail grid when > 1 image exists
- Gallery thumbnail click opens lightbox with full image
- Lightbox closes on Escape key and backdrop click
- Description rendered from Markdown to HTML
- Practical info section: shown only when data exists (opening hours, prices, website)
- External website links open in new tab with `rel="noopener"`
- Print button triggers `window.print()`
- Basic print CSS hides interactive elements
- Back link to catalog
- Nearby objects section container present (empty, populated by RFC-007)
- Page responsive on mobile and desktop

## Do NOT

- Do not implement nearby objects loading (that's RFC-007)
- Do not enhance the print layout (that's RFC-007)
- Do not modify the catalog page or filters
- Do not create the homepage or article pages

## Reference

Read the full RFC at: `.ai/RFCs/RFC-006-Object-Detail.md`
