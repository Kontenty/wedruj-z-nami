# Implementation Prompt: RFC-004

**Title:** Public Pages (Homepage, News)  
**ID:** RFC-004  
**Brief Description:** Build all Blade-based public pages for the informational layer: homepage with project description and latest objects, news listing page, and news detail page. Includes shared layout, navigation, responsive design, and Tailwind Typography.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*

---

You are implementing RFC-004 for the Kanon project. This RFC creates the public-facing informational layer.

## Prerequisites

- RFC-001 must be completed (models and seed data exist)

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
- News body rendered from Markdown using `Str::markdown()`
- Unpublished objects/news entries never appear on public pages
- Responsive: mobile hamburger nav, card grids adapt to screen size
- Tailwind Typography used for prose styling on news detail pages
- Public content language is Polish (labels/headings/navigation copy)

## Do NOT

- Do not install Filament or create CMS pages
- Do not create the interactive catalog (that's RFC-005)
- Do not create the object detail page (that's RFC-006)
- Do not create a contact page or contact email flow in this RFC
- Do not modify existing Fortify auth routes or views

## Reference

Read the full RFC at: `.ai/RFCs/RFC-004-Public-Pages.md`
