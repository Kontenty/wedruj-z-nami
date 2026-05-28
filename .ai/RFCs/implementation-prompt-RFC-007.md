# Implementation Prompt: RFC-007

**Title:** Advanced Features (Nearby Objects, Print, Polish)  
**ID:** RFC-007  
**Brief Description:** Implement nearby objects with automatic 5km→20km radius fallback, enhanced print layout, WCAG 2.1 AA accessibility audit, performance optimization, and final polish to complete the beta scope.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*

---

You are implementing RFC-007 for the Kanon project. This is the **final RFC** that completes the beta. It adds the remaining features and ensures quality across the entire application.

## Prerequisites

- RFC-001 must be completed (PostGIS spatial queries)
- RFC-005 must be completed (catalog page exists)
- RFC-006 must be completed (object detail page with nearby placeholder)

## What to Build

1. **Nearby objects API endpoint** — `/obiekty/{slug}/nearby` returning JSON with objects and radius info
2. **Dynamic nearby objects loading** — JavaScript on object detail page fetches and renders nearby objects
3. **Enhanced print CSS** — comprehensive print stylesheet for object detail pages
4. **WCAG 2.1 AA accessibility fixes** — skip links, aria attributes, focus management, contrast, keyboard nav
5. **Performance optimization** — eager loading, lazy images, caching, spatial index verification
6. **Contact form email delivery** — Mailable class and email template (if not working from RFC-004)
7. **Custom 404 page**
8. **Final design polish** — consistent spacing, typography, colors across all pages
9. **Write Pest tests** for nearby objects endpoint, contact email, accessibility basics

## Key Files to Create/Modify

- `routes/web.php` — add `/obiekty/{slug}/nearby` route
- `app/Http/Controllers/ObjectController.php` — add `nearby()` method
- `resources/views/objects/show.blade.php` — replace nearby placeholder with dynamic loading
- `resources/css/print.css` — enhanced print stylesheet
- `app/Mail/ContactMessage.php` (if not created in RFC-004)
- `resources/views/emails/contact.blade.php` (if not created in RFC-004)
- `resources/views/errors/404.blade.php` — custom 404 page
- Various view files — accessibility improvements (aria labels, skip links, focus states)
- `tests/Feature/NearbyObjectsTest.php`
- `tests/Feature/ContactEmailTest.php`
- `tests/Feature/AccessibilityTest.php`

## Critical Requirements

### Nearby Objects
- `/obiekty/{slug}/nearby` returns JSON: `{ objects: [...], radius_km: 5|20, has_results: bool }`
- 5km radius tried first; falls back to 20km when no 5km results
- Maximum 6 nearby objects returned
- Current object excluded from results
- Objects ordered by distance (nearest first)
- Object detail page loads nearby objects via fetch after page load
- Loading skeleton shown during fetch
- Empty state with catalog link when no nearby objects at 20km
- Radius information displayed to user

### Print
- Print CSS hides: header, footer, nav, nearby objects, print button, lightbox
- Print preserves: title, metadata, main image, gallery, description, practical info
- Readable on A4 paper
- External link URLs displayed in print
- Images scale to fit print width
- Page breaks handled (no orphaned sections)

### Accessibility
- Skip-to-content link on all public pages
- All `<img>` elements have meaningful `alt` text
- All form inputs have associated `<label>` elements
- Color contrast ≥ 4.5:1 (normal text), ≥ 3:1 (large text)
- Keyboard navigation works on all interactive elements
- Focus states visible (focus ring)
- Map does not trap keyboard focus
- Category accordion keyboard accessible (Enter/Space/arrows)
- Bottom sheet traps focus while open, returns on close
- Filter changes announced via `aria-live="polite"`

### Performance
- No N+1 queries on any page (verify with `DB::enableQueryLog()`)
- `loading="lazy"` on below-fold images
- Homepage queries cached (5 minute TTL)
- Spatial queries use GiST index (verify with EXPLAIN)

### Contact
- Contact form sends email (or logs to `storage/logs/laravel.log` in dev)
- Email contains name, email, message
- Success message displayed after submission

### Final Polish
- Custom 404 page with navigation links
- Consistent Tailwind spacing and typography across all pages
- Primary color consistent for CTAs and links
- All async operations have loading indicators
- All error conditions have user-friendly messages
- All empty conditions have helpful guidance

## Do NOT

- Do not add user accounts, favorites, reviews, or comments
- Do not add tags, author profiles, or scheduled publishing
- Do not expand the blog into a full publishing platform
- Do not change the CMS workflow
- Do not add map marker clustering (unless object count exceeds 100)

## Reference

Read the full RFC at: `.ai/RFCs/RFC-007-Advanced-Features.md`
