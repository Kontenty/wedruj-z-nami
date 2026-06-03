# Implementation Prompt: RFC-004

**Title:** Public Pages (Homepage, News)  
**ID:** RFC-004  
**Brief Description:** Build all Blade-based public pages for the informational layer: homepage with project description and latest objects, news listing page, and news detail page. Includes shared layout, navigation, responsive design, and Tailwind Typography.

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "news" = _aktualności_

---

You are implementing RFC-004 for the Kanon project. This RFC creates the public-facing informational layer.

## Prerequisites

- RFC-001 must be completed (models and seed data exist)
- RFC-002 must be completed (media URLs, thumbnails, and cover images exist)

## What to Build

1. **Shared public layout** (`layouts/public.blade.php`) with header, footer, responsive navigation
2. **Homepage** (`HomeController` + `home.blade.php`) with hero, "for whom" section, latest objects grid, latest news teaser
3. **News listing** (`NewsController::index` + `news/index.blade.php`) with paginated news grid and latest objects section
4. **News detail** (`NewsController::show` + `news/show.blade.php`) with Markdown rendering, back link, contextual CTA
5. **Navigation components** (public header with mobile hamburger, footer)
6. **Install Tailwind Typography** for Markdown prose styling
7. **Write Pest tests** for all public page controllers and views

## Key Files to Create/Modify

- `routes/web.php` — add public routes
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/NewsController.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/components/public-header.blade.php`
- `resources/views/components/public-footer.blade.php`
- `resources/views/home.blade.php`
- `resources/views/news/index.blade.php`
- `resources/views/news/show.blade.php`
- `tests/Feature/HomePageTest.php`
- `tests/Feature/NewsPageTest.php`

## Critical Requirements

- Homepage shows up to 4 latest published objects and 3 latest published news entries
- News listing paginates (12 per page), reverse chronological
- News body rendered from Markdown using `Str::markdown()` with XSS protection
- Unpublished objects/news entries never appear on public pages
- Responsive: mobile hamburger nav, card grids adapt to screen size
- Tailwind Typography used for prose styling on news detail pages
- Public content language is Polish (labels/headings/navigation copy)
- Mobile menu powered by Alpine.js via CDN

## Grilling Decisions Applied

These decisions were made during the plan review:

1. **Inertia vs. Blade Homepage:** Replace the Inertia home route with Blade `HomeController`. The existing `Welcome.svelte` is Laravel boilerplate, not project-specific content.
2. **Dead `catalog.show` Links:** Use placeholder `#` links for object cards until RFC-006 creates the `catalog.show` route.
3. **Mobile Hamburger Menu:** Use Alpine.js via CDN. No JavaScript files needed — all logic inline in the Blade component.
4. **Dark Mode:** No dark mode for public Blade pages. Keep it simple — light theme only.
5. **`Str::markdown()` Security:** Use `html_input` with `strip` and `allow_unsafe_links` with `false` to prevent XSS.
6. **Pagination:** Standard Laravel pagination with `{{ $news->links() }}`.
7. **Caching:** Skip caching for MVP. Simple queries (limit 4/3) don't warrant cache invalidation complexity.
8. **News Page PRD Deviation:** Accept flat listing approach with objects at bottom. PRD 5.4's "newly added objects" is a soft requirement.
9. **`wojewodztwo` vs `voivodeship`:** Fixed in RFC-004 document. Code should use `voivodeship` as defined in the model.
10. **Navigation Links:** Keep `/katalog` links as-is (404 until RFC-005). Users will get a 404 until the catalog is implemented.

## Do NOT

- Do not install Filament or create CMS pages
- Do not create the interactive catalog (that's RFC-005)
- Do not create the object detail page (that's RFC-006)
- Do not create a contact page or contact email flow in this RFC
- Do not modify existing Fortify auth routes or views
- Do not add dark mode support
- Do not implement homepage caching

## Reference

Read the full RFC at: `.ai/RFCs/RFC-004-Public-Pages.md`
